<?php

namespace App\Http\Controllers\PublicSite;

use App\Ejma\AssemblyType;
use App\Ejma\BellowsType;
use App\Ejma\CheckResult;
use App\Ejma\Data\MaterialRepository;
use App\Ejma\Data\Tooling;
use App\Ejma\DesignSearch;
use App\Ejma\EjmaCalculator;
use App\Ejma\EjmaInputException;
use App\Ejma\FormingCondition;
use App\Ejma\FormingMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\EjmaCalculationRequest;
use App\Mail\EjmaCalculationReport;
use App\Models\EjmaCalculation;
use App\Support\PublicSeo;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class EjmaCalculatorController extends Controller
{
    private const PATH = '/toolkit/ejma-bellows-calculator/';

    private const CALCULATIONS_PER_HOUR = 5;

    /** The fragment after the trailing slash stops the URL generator trimming it (a slash redirect would drop flashed errors). */
    private const FORM_URL = self::PATH.'#calculator-form';

    /** Form fields an engine error may be reported on; anything else is shown as a general error. */
    private const FORM_FIELDS = [
        'units', 'bellows_type', 'design_pressure', 'design_temperature', 'required_cycles', 'x_comp', 'x_ext',
        'lateral', 'angular', 'Db', 'w', 't', 'n', 'N', 'q', 'rm', 'Lt', 'tc', 'Lc', 'Ld', 'Ar', 'Atc', 'Zc', 'ng',
        'r', 'Dr', 'Dm', 'fw', 'bellows_material', 'collar_material', 'reinforcement_material', 'forming_condition',
        'forming_method', 'Cwb', 'Cwc', 'Cwr', 'override_Sab', 'override_EbRt', 'override_EbDt', 'override_Sy',
        'override_Sym', 'assembly_type', 'Lu', 'tie_rods', 'angular_2', 'spool_weight', 'spool_angle', 'mode',
    ];

    public function index(MaterialRepository $materials, Tooling $tooling)
    {
        $siteSettings = DB::table('site_settings')->pluck('value', 'key')->toArray();

        $breadcrumbs = [
            ['name' => 'Home', 'url' => '/'],
            ['name' => 'Engineering Toolkit', 'url' => '/toolkit/'],
            ['name' => 'EJMA Bellows Calculator', 'url' => self::PATH],
        ];

        $seo = [
            'meta_title' => 'EJMA Bellows Calculator for Metal Expansion Joints | PT Misuba Guna Indonesia',
            'meta_description' => 'Check a metal bellows expansion joint design against EJMA Standards: pressure stresses, fatigue life and squirm for unreinforced, reinforced and toroidal bellows.',
            'canonical_url' => self::PATH,
            'og_title' => 'EJMA Bellows Calculator | Misuba Guna',
            'og_description' => 'Free online EJMA design check for unreinforced, reinforced and toroidal metal bellows, reviewed by our engineering team.',
            'robots_index' => true,
            'robots_follow' => true,
            'schema_json' => [
                PublicSeo::organizationSchema($siteSettings),
                PublicSeo::breadcrumbSchema($breadcrumbs),
                PublicSeo::webApplicationSchema(
                    'EJMA Bellows Calculator',
                    'Online design check of metal bellows expansion joints per EJMA Standards for unreinforced, reinforced and toroidal bellows.',
                    self::PATH
                ),
            ],
        ];

        $materialOptions = [
            'bellows' => $materials->availableFor('bellows'),
            'collar' => $materials->availableFor('collar'),
            'reinforcement' => $materials->availableFor('reinforcement'),
        ];
        // Bellows materials offered in check mode only (no unreinforced fatigue constants, ruling P3-R2).
        $designMaterialIds = array_map(fn ($m) => $m->id, EjmaCalculationRequest::designMaterials($materials));
        $checkOnlyMaterials = array_values(array_diff(
            array_map(fn ($m) => $m->id, $materialOptions['bellows']),
            $designMaterialIds,
        ));
        $designAvailable = $tooling->isAvailable();
        $units = EjmaCalculationRequest::UNITS;
        $quantities = EjmaCalculationRequest::QUANTITIES;
        $bellowsTypes = BellowsType::cases();
        $assemblyTypes = AssemblyType::cases();
        $formingConditions = FormingCondition::cases();
        $formingMethods = FormingMethod::cases();
        $result = session('ejma_result');
        $contactTo = config('mail.contact_to');

        return view('public.toolkit.ejma-bellows-calculator', compact(
            'seo', 'siteSettings', 'breadcrumbs', 'materialOptions', 'units', 'quantities',
            'bellowsTypes', 'assemblyTypes', 'formingConditions', 'formingMethods', 'result', 'contactTo',
            'designAvailable', 'checkOnlyMaterials',
        ));
    }

    public function store(EjmaCalculationRequest $request, EjmaCalculator $calculator, DesignSearch $search)
    {
        if ($request->isHoneypot()) {
            return redirect()->to(self::FORM_URL);
        }

        $this->guardCalculationAllowance($request->ip());

        $design = null;
        try {
            if ($request->isDesign()) {
                $design = $search->search($request->toDesignRequest());
                // The best candidate's check, or an empty result carrying the review note (spec §4).
                $result = $design->best?->result
                    ?? new CheckResult(BellowsType::Unreinforced, [], [], [$design->reviewNote()]);
                $verdict = $design->verdict();
            } else {
                $result = $calculator->calculate($request->toCalculationRequest());
                $verdict = $result->verdict();
            }
            $results = $result->toArray();
            // Stored JSON must agree with the row: a design Review has an empty result that computes some other verdict.
            $results['verdict'] = $verdict->value;
        } catch (EjmaInputException $e) {
            throw ValidationException::withMessages($this->engineError($e, $request->bellowsType()))
                ->redirectTo(self::FORM_URL);
        } catch (\InvalidArgumentException|\ArithmeticError $e) {
            // Not a visitor input problem (e.g. bad material data): log it, keep the form usable.
            report($e);

            throw ValidationException::withMessages([
                'calculation' => 'We could not complete this calculation. Please check the inputs or contact us.',
            ])->redirectTo(self::FORM_URL);
        }

        RateLimiter::hit(self::limiterKey($request->ip()), 3600);

        $attributes = [
            'bellows_type' => $result->type->value,
            'assembly_type' => $request->assemblyType()->value,
            'mode' => $design !== null ? 'design' : 'check',
            'verdict' => $verdict->value,
            'inputs' => $request->storedInputs(),
            'results' => $results,
            'design' => $design?->toArray(),
            'contact' => $request->contact(),
            'ip_address' => $request->ip(),
        ];

        try {
            $calculation = $this->persist($attributes);
        } catch (UniqueConstraintViolationException) {
            $calculation = $this->persist($attributes);
        }

        $this->sendReport($calculation);

        // Keep the input so engineers can tweak and resubmit.
        return redirect()->to(self::PATH.'#result')
            ->withInput($request->except('website'))
            ->with('ejma_result', [
                'reference' => $calculation->reference,
                'verdict' => $calculation->verdict,
                'mode' => $calculation->mode,
            ]);
    }

    /**
     * Only successful calculations count towards the hourly limit (hit() runs after success), so visitors
     * fixing validation errors are never locked out. The limit is checked before the engine or search runs.
     * The route throttle is just a flood backstop.
     */
    private function guardCalculationAllowance(?string $ip): void
    {
        if (RateLimiter::tooManyAttempts(self::limiterKey($ip), self::CALCULATIONS_PER_HOUR)) {
            throw ValidationException::withMessages([
                'calculation' => 'You have reached the limit of '.self::CALCULATIONS_PER_HOUR.' calculations per hour. Please try again later or contact us.',
            ])->redirectTo(self::FORM_URL);
        }
    }

    private static function limiterKey(?string $ip): string
    {
        return 'ejma-calc:'.$ip;
    }

    private function persist(array $attributes): EjmaCalculation
    {
        return DB::transaction(fn () => EjmaCalculation::create(
            ['reference' => EjmaCalculation::nextReference()] + $attributes
        ));
    }

    /** @return array<string, string> */
    private function engineError(EjmaInputException $e, BellowsType $type): array
    {
        if ($e->field === 'geometry') {
            $toroidal = $type === BellowsType::Toroidal;

            return [$toroidal ? 'r' : 'rm' => $toroidal
                ? 'This convolution geometry is outside the range of the EJMA design charts. Check the convolution radius r, mean diameter Dm and ply thickness t, or contact us for an engineering review.'
                : 'This convolution geometry is outside the range of the EJMA design charts. Check the mean radius rm against the convolution height w (rm should not exceed w/2), the ply thickness t and the diameter Db, or contact us for an engineering review.',
            ];
        }

        $field = in_array($e->field, self::FORM_FIELDS, true) ? $e->field : 'calculation';

        return [$field => $e->getMessage()];
    }

    /** Emails the full calculation to the engineering team; a failure is reported but never shown to the visitor. */
    private function sendReport(EjmaCalculation $calculation): void
    {
        try {
            Mail::to(config('mail.contact_to'))->send(new EjmaCalculationReport($calculation));
            $calculation->forceFill(['emailed_at' => now()])->save();
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
