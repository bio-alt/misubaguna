<?php

namespace App\Http\Requests;

use App\Ejma\AssemblyGeometry;
use App\Ejma\AssemblyType;
use App\Ejma\BellowsGeometry;
use App\Ejma\BellowsType;
use App\Ejma\CalculationRequest;
use App\Ejma\Data\Material;
use App\Ejma\Data\MaterialRepository;
use App\Ejma\DesignConditions;
use App\Ejma\FormingCondition;
use App\Ejma\FormingMethod;
use App\Ejma\Units;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class EjmaCalculationRequest extends FormRequest
{
    /** Fragment after the trailing slash keeps the URL generator from trimming it (a slash redirect would drop the flashed errors). */
    protected $redirect = '/toolkit/ejma-bellows-calculator/#calculator-form';

    /**
     * Physical quantity of every numeric form field. Metric is the default unit system;
     * the engine works in US customary units only.
     */
    public const QUANTITIES = [
        'design_pressure' => 'pressure',
        'design_temperature' => 'temperature',
        'required_cycles' => 'count',
        'x_comp' => 'length',
        'x_ext' => 'length',
        'lateral' => 'length',
        'angular' => 'angle',
        'Db' => 'length',
        'w' => 'length',
        't' => 'length',
        'n' => 'count',
        'N' => 'count',
        'q' => 'length',
        'rm' => 'length',
        'Lt' => 'length',
        'tc' => 'length',
        'Lc' => 'length',
        'Ld' => 'length',
        'Ar' => 'area',
        'Atc' => 'area',
        'Zc' => 'section_modulus',
        'ng' => 'count',
        'r' => 'length',
        'Dr' => 'length',
        'Dm' => 'length',
        'fw' => 'spring_rate',
        'Cwb' => 'ratio',
        'Cwc' => 'ratio',
        'Cwr' => 'ratio',
        'override_Sab' => 'stress',
        'override_EbRt' => 'modulus',
        'override_EbDt' => 'modulus',
        'override_Sy' => 'stress',
        'override_Sym' => 'stress',
        'Lu' => 'length',
        'tie_rods' => 'count',
        'angular_2' => 'angle',
        'spool_weight' => 'force',
        'spool_angle' => 'angle',
    ];

    /** Unit labels per quantity: [metric, us]. */
    public const UNITS = [
        'pressure' => ['bar(g)', 'psig'],
        'temperature' => ['°C', '°F'],
        'length' => ['mm', 'in'],
        'angle' => ['deg', 'deg'],
        'area' => ['mm²', 'in²'],
        'section_modulus' => ['mm³', 'in³'],
        'spring_rate' => ['N/mm', 'lbf/in'],
        'force' => ['N', 'lbf'],
        'stress' => ['MPa', 'psi'],
        'modulus' => ['GPa', '10⁶ psi'],
        'count' => ['', ''],
        'ratio' => ['', ''],
    ];

    /**
     * Generous engineering upper bounds per quantity [metric, us]; they keep values such as
     * 1e400 (INF) away from the engine. Temperature also has a lower bound (see rules()).
     */
    public const LIMITS = [
        'pressure' => [1000, 14500],
        'temperature' => [1200, 2192],
        'length' => [10000, 394],
        'area' => [1000000, 1550],
        'section_modulus' => [100000000, 6102],
        'spring_rate' => [10000000, 57000000],
        'force' => [10000000, 2248000],
        'stress' => [10000, 1450000],
        'modulus' => [1000, 145],
    ];

    /** Fields that only apply to some bellows types; others are ignored for the calculation. */
    private const TYPE_FIELDS = [
        'unreinforced' => ['w', 'rm', 'Lt', 'tc', 'Lc'],
        'reinforced' => ['w', 'rm', 'Lt', 'tc', 'Ld', 'Ar', 'Atc', 'Zc', 'ng', 'fw'],
        'toroidal' => ['tc', 'Ld', 'Ar', 'Atc', 'Zc', 'ng', 'r', 'Dr', 'Dm', 'fw'],
    ];

    private const COMMON_FIELDS = [
        'design_pressure', 'design_temperature', 'required_cycles', 'x_comp', 'x_ext', 'lateral', 'angular',
        'Db', 't', 'n', 'N', 'q', 'Cwb', 'Cwc', 'Cwr',
        'override_Sab', 'override_EbRt', 'override_EbDt', 'override_Sy', 'override_Sym',
    ];

    /** Assembly-only fields (spec §4); dropped for every other assembly. */
    private const ASSEMBLY_FIELDS = [
        'single' => [],
        'universal' => ['Lu', 'spool_weight', 'spool_angle'],
        'tied_universal' => ['Lu', 'tie_rods', 'spool_weight', 'spool_angle'],
        'hinged' => [],
        'gimbal' => ['angular_2'],
    ];

    /**
     * Movements restrained by the assembly hardware (ruling P2-R3): the form disables them and
     * stale values are ignored. Tied universal keeps x_comp as the axial growth between the
     * tie-rod plates; hinged and gimbal joints take angular rotation only (x = y = 0).
     */
    private const RESTRAINED_FIELDS = [
        'single' => [],
        'universal' => [],
        'tied_universal' => ['x_ext'],
        'hinged' => ['x_comp', 'x_ext', 'lateral'],
        'gimbal' => ['x_comp', 'x_ext', 'lateral'],
    ];

    public function authorize(): bool
    {
        return true;
    }

    /** Bots fill the hidden "website" field; such requests skip validation and are discarded. */
    public function isHoneypot(): bool
    {
        return $this->filled('website');
    }

    public function validateResolved(): void
    {
        if ($this->isHoneypot()) {
            return;
        }

        parent::validateResolved();
    }

    public function rules(): array
    {
        $materials = app(MaterialRepository::class);
        $ids = fn (string $role) => array_map(fn (Material $m) => $m->id, $materials->availableFor($role));
        $dim = ['nullable', 'numeric', 'gt:0'];
        $uShaped = 'required_if:bellows_type,unreinforced,reinforced';

        $rules = [
            'units' => ['required', Rule::in(['metric', 'us'])],
            'bellows_type' => ['required', Rule::enum(BellowsType::class)],
            'assembly_type' => ['nullable', Rule::enum(AssemblyType::class)],

            'design_pressure' => ['required', 'numeric', 'gte:0'],
            'design_temperature' => ['required', 'numeric'],
            'required_cycles' => ['required', 'integer', 'min:1', 'max:1000000000'],
            // Restrained movements and other assemblies' fields are excluded before validation (ruling P2-R3).
            'x_comp' => ['exclude_if:assembly_type,hinged,gimbal', 'nullable', 'numeric', 'gte:0'],
            'x_ext' => ['exclude_if:assembly_type,tied_universal,hinged,gimbal', 'nullable', 'numeric', 'gte:0'],
            'lateral' => ['exclude_if:assembly_type,hinged,gimbal', 'nullable', 'numeric', 'gte:0'],
            'angular' => ['nullable', 'numeric', 'gte:0', 'lt:90'],
            'angular_2' => ['exclude_unless:assembly_type,gimbal', 'nullable', 'numeric', 'gte:0', 'lt:90'],
            'Lu' => ['exclude_unless:assembly_type,universal,tied_universal', 'required', 'numeric', 'gt:0'],
            'tie_rods' => ['exclude_unless:assembly_type,tied_universal', 'required', 'integer', 'min:2', 'max:100'],
            'spool_weight' => ['exclude_unless:assembly_type,universal,tied_universal', 'nullable', 'numeric', 'gte:0'],
            'spool_angle' => ['exclude_unless:assembly_type,universal,tied_universal', 'required_with:spool_weight', 'nullable', 'numeric', 'gte:0', 'lte:90'],

            'Db' => ['required', 'numeric', 'gt:0'],
            't' => ['required', 'numeric', 'gt:0'],
            'n' => ['required', 'integer', 'min:1', 'max:20'],
            'N' => ['required', 'integer', 'min:1', 'max:500'],
            'q' => ['required', 'numeric', 'gt:0'],
            'w' => [$uShaped, ...$dim],
            'rm' => [$uShaped, ...$dim],
            'Lt' => [$uShaped, ...$dim],
            'tc' => ['required_if:bellows_type,toroidal', 'nullable', 'numeric', 'gte:0'],
            'Lc' => $dim,
            'Ld' => ['required_if:bellows_type,toroidal', ...$dim],
            'Ar' => ['required_if:bellows_type,reinforced,toroidal', ...$dim],
            'Atc' => ['required_if:bellows_type,toroidal', ...$dim],
            'Zc' => ['required_if:bellows_type,toroidal', ...$dim],
            'ng' => ['required_if:bellows_type,toroidal', 'nullable', 'integer', 'min:1', 'max:200'],
            'r' => ['required_if:bellows_type,toroidal', ...$dim],
            'Dr' => ['required_if:bellows_type,toroidal', ...$dim],
            'Dm' => ['required_if:bellows_type,toroidal', ...$dim],
            'fw' => $dim,

            'bellows_material' => ['required', 'string', Rule::in($ids('bellows'))],
            'collar_material' => ['required_if:bellows_type,toroidal', 'nullable', 'string', Rule::in($ids('collar'))],
            'reinforcement_material' => ['required_if:bellows_type,reinforced,toroidal', 'nullable', 'string', Rule::in($ids('reinforcement'))],
            'forming_condition' => ['required', Rule::enum(FormingCondition::class)],
            'forming_method' => ['required', Rule::enum(FormingMethod::class)],
            'Cwb' => ['nullable', 'numeric', 'gt:0', 'lte:1'],
            'Cwc' => ['nullable', 'numeric', 'gt:0', 'lte:1'],
            'Cwr' => ['nullable', 'numeric', 'gt:0', 'lte:1'],
            'override_Sab' => $dim,
            'override_EbRt' => $dim,
            'override_EbDt' => $dim,
            'override_Sy' => $dim,
            'override_Sym' => $dim,

            'name' => ['required', 'string', 'max:120'],
            'company' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email', 'max:160'],
            'phone' => ['required', 'string', 'max:40'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];

        $system = $this->input('units') === 'us' ? 1 : 0;
        foreach (self::QUANTITIES as $field => $kind) {
            if (isset(self::LIMITS[$kind])) {
                $rules[$field][] = 'max:'.self::LIMITS[$kind][$system];
            }
        }
        $rules['design_temperature'][] = 'min:'.($system === 1 ? -328 : -200);

        return $rules;
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }
                $type = $this->input('bellows_type');
                if ($type !== 'toroidal' && (float) $this->input('w') <= (int) $this->input('n') * (float) $this->input('t')) {
                    $validator->errors()->add('w', 'Convolution height w must be larger than the total ply thickness n·t.');
                }
                if ($type !== 'toroidal' && (float) $this->input('tc') > 0 && blank($this->input('collar_material'))) {
                    $validator->errors()->add('collar_material', 'Select a collar material when a collar thickness tc is given.');
                }
            },
        ];
    }

    public function attributes(): array
    {
        return [
            'Db' => 'bellows inside diameter Db',
            'w' => 'convolution height w',
            't' => 'ply thickness t',
            'n' => 'number of plies n',
            'N' => 'number of convolutions N',
            'q' => 'convolution pitch q',
            'rm' => 'mean radius rm',
            'Lt' => 'tangent length Lt',
            'tc' => 'collar thickness tc',
            'Lc' => 'collar length Lc',
            'Ld' => 'length Ld',
            'Ar' => 'reinforcing member area Ar',
            'Atc' => 'collar area Atc',
            'Zc' => 'collar section modulus Zc',
            'ng' => 'number of gussets ng',
            'r' => 'convolution radius r',
            'Dr' => 'reinforcing member mean diameter Dr',
            'Dm' => 'mean diameter Dm',
            'fw' => 'working spring rate fw',
            'x_comp' => 'axial compression',
            'x_ext' => 'axial extension',
            'lateral' => 'lateral movement',
            'angular' => 'angular rotation',
            'Cwb' => 'weld efficiency Cwb',
            'Cwc' => 'weld efficiency Cwc',
            'Cwr' => 'weld efficiency Cwr',
            'override_Sab' => 'allowable stress override',
            'override_EbRt' => 'modulus override (room temperature)',
            'override_EbDt' => 'modulus override (design temperature)',
            'override_Sy' => 'yield strength override',
            'override_Sym' => 'material test report yield override',
            'assembly_type' => 'assembly',
            'Lu' => 'length Lu',
            'tie_rods' => 'number of tie rods',
            'angular_2' => 'second-plane rotation',
            'spool_weight' => 'centre-spool weight',
            'spool_angle' => 'centre-spool angle',
        ];
    }

    public function messages(): array
    {
        return [
            'required_if' => 'The :attribute field is required for this bellows type.',
            'bellows_material.in' => 'Selected material is not available.',
            'collar_material.in' => 'Selected material is not available.',
            'reinforcement_material.in' => 'Selected material is not available.',
            'Lu.required' => 'Enter Lu, the distance between the outermost convolution ends, for a universal expansion joint.',
            'tie_rods.required' => 'Enter the number of tie rods for a tied universal expansion joint.',
            'tie_rods.min' => 'A tied universal expansion joint needs at least 2 tie rods.',
            'spool_angle.required_with' => 'Enter the centre-spool angle when a centre-spool weight is given.',
        ];
    }

    public function bellowsType(): BellowsType
    {
        return BellowsType::from($this->input('bellows_type'));
    }

    /** The submitted assembly; Phase 1 payloads without one are single expansion joints. */
    public function assemblyType(): AssemblyType
    {
        return AssemblyType::tryFrom((string) $this->input('assembly_type')) ?? AssemblyType::Single;
    }

    /** Numeric fields that apply to the selected bellows type and assembly, as entered (nulls removed). */
    private function relevantNumbers(): array
    {
        $assembly = $this->assemblyType()->value;
        $fields = array_diff(
            [...self::COMMON_FIELDS, ...self::TYPE_FIELDS[$this->bellowsType()->value], ...self::ASSEMBLY_FIELDS[$assembly]],
            self::RESTRAINED_FIELDS[$assembly],
        );
        if (blank($this->input('spool_weight'))) {
            $fields = array_diff($fields, ['spool_angle']);
        }
        $out = [];
        foreach ($fields as $f) {
            $v = $this->input($f);
            if ($v !== null && $v !== '') {
                $out[$f] = $v;
            }
        }

        return $out;
    }

    /** Value converted to US customary units (in, psi, °F, deg, ...). */
    public static function toUs(string $field, float $v, string $units): float
    {
        $kind = self::QUANTITIES[$field];
        if ($units === 'us') {
            return $kind === 'modulus' ? $v * 1e6 : $v;
        }

        return match ($kind) {
            'pressure' => Units::barToPsi($v),
            'temperature' => Units::cToF($v),
            'length' => Units::mmToIn($v),
            'area' => $v / 25.4 ** 2,
            'section_modulus' => $v / 25.4 ** 3,
            'spring_rate' => Units::nToLbf($v) * 25.4,
            'force' => Units::nToLbf($v),
            'stress' => Units::mpaToPsi($v),
            'modulus' => Units::gpaToPsi($v),
            default => $v,
        };
    }

    /** US customary value (psi for moduli) converted to metric (mm, bar(g), °C, MPa, GPa, ...). */
    public static function toMetric(string $field, float $us): float
    {
        return match (self::QUANTITIES[$field]) {
            'pressure' => Units::psiToBar($us),
            'temperature' => Units::fToC($us),
            'length' => Units::inToMm($us),
            'area' => $us * 25.4 ** 2,
            'section_modulus' => $us * 25.4 ** 3,
            'spring_rate' => Units::lbfToN($us) / 25.4,
            'force' => Units::lbfToN($us),
            'stress' => $us / Units::mpaToPsi(1.0),
            'modulus' => $us / Units::gpaToPsi(1.0),
            default => $us,
        };
    }

    /** @return array<string, float> numeric inputs in US customary units */
    public function usValues(): array
    {
        $units = $this->input('units');
        $out = [];
        foreach ($this->relevantNumbers() as $f => $v) {
            $out[$f] = self::toUs($f, (float) $v, $units);
        }

        return $out;
    }

    /**
     * Storage form (spec §3.3): the submitted values with their units, plus the US
     * values used by the engine and the metric equivalents.
     *
     * @return array<string, mixed>
     */
    public function storedInputs(): array
    {
        $units = $this->input('units');
        $entered = [];
        foreach ($this->relevantNumbers() as $f => $v) {
            $unit = self::UNITS[self::QUANTITIES[$f]][$units === 'us' ? 1 : 0];
            $entered[$f] = ['value' => (string) $v, 'unit' => $unit];
        }
        $us = $this->usValues();
        $metric = [];
        foreach ($us as $f => $v) {
            $metric[$f] = self::toMetric($f, $v);
        }
        $type = $this->bellowsType();

        return [
            'units' => $units,
            'bellows_type' => $type->value,
            'assembly_type' => $this->assemblyType()->value,
            'entered' => $entered,
            'us' => $us,
            'metric' => $metric,
            'materials' => [
                'bellows' => $this->input('bellows_material'),
                'collar' => $this->collarMaterial(),
                'reinforcement' => $type === BellowsType::Unreinforced ? null : $this->input('reinforcement_material'),
            ],
            'forming_condition' => $this->input('forming_condition'),
            'forming_method' => $this->input('forming_method'),
        ];
    }

    /** The collar material only counts when a collar exists (always for toroidal, else tc > 0). */
    private function collarMaterial(): ?string
    {
        $id = $this->input('collar_material');
        $hasCollar = $this->bellowsType() === BellowsType::Toroidal || (float) $this->input('tc') > 0;

        return $hasCollar && ! blank($id) ? (string) $id : null;
    }

    public function toCalculationRequest(): CalculationRequest
    {
        $type = $this->bellowsType();
        $us = $this->usValues();
        $get = fn (string $f): ?float => $us[$f] ?? null;
        $int = fn (string $f): int => (int) ($us[$f] ?? 0);

        $geometry = new BellowsGeometry(
            type: $type,
            Db: $get('Db'),
            t: $get('t'),
            n: $int('n'),
            N: $int('N'),
            q: $get('q'),
            w: $get('w'),
            rm: $get('rm'),
            Lt: $get('Lt'),
            tc: $get('tc') ?? 0.0,
            Lc: $get('Lc') ?? 0.0,
            Ld: $get('Ld'),
            Ar: $get('Ar') ?? 0.0,
            Atc: $get('Atc') ?? 0.0,
            Zc: $get('Zc') ?? 0.0,
            ng: $int('ng'),
            r: $get('r'),
            Dr: $get('Dr'),
            DmToroidal: $get('Dm'),
            fw: $get('fw'),
        );

        $conditions = new DesignConditions(
            P: $get('design_pressure'),
            xComp: $get('x_comp') ?? 0.0,
            xExt: $get('x_ext') ?? 0.0,
            y: $get('lateral') ?? 0.0,
            theta: Units::degToRad($get('angular') ?? 0.0),
            requiredCycles: $int('required_cycles'),
            assembly: new AssemblyGeometry(
                type: $this->assemblyType(),
                Lu: $get('Lu'),
                tieRods: $int('tie_rods'),
                theta2: Units::degToRad($get('angular_2') ?? 0.0),
                Wcs: $get('spool_weight'),
                thetaU: Units::degToRad($get('spool_angle') ?? 0.0),
            ),
        );

        $overrides = [];
        foreach (['Sab', 'EbRt', 'EbDt', 'Sy', 'Sym'] as $key) {
            if (isset($us['override_'.$key])) {
                $overrides[$key] = $us['override_'.$key];
            }
        }

        return new CalculationRequest(
            geometry: $geometry,
            conditions: $conditions,
            designTempF: $get('design_temperature'),
            bellowsMaterial: (string) $this->input('bellows_material'),
            collarMaterial: $this->collarMaterial(),
            reinforcementMaterial: $type === BellowsType::Unreinforced ? null : $this->input('reinforcement_material'),
            condition: FormingCondition::from($this->input('forming_condition')),
            method: FormingMethod::from($this->input('forming_method')),
            Cwb: $get('Cwb') ?? 1.0,
            Cwc: $get('Cwc') ?? 1.0,
            Cwr: $get('Cwr') ?? 1.0,
            overrides: $overrides,
        );
    }

    /** @return array{name: string, company: string, email: string, phone: string, notes: ?string} */
    public function contact(): array
    {
        return [
            'name' => (string) $this->input('name'),
            'company' => (string) $this->input('company'),
            'email' => (string) $this->input('email'),
            'phone' => (string) $this->input('phone'),
            'notes' => $this->input('notes'),
        ];
    }
}
