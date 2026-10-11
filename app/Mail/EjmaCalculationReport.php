<?php

namespace App\Mail;

use App\Ejma\CheckLine;
use App\Ejma\Data\MaterialRepository;
use App\Ejma\LineStatus;
use App\Ejma\Units;
use App\Http\Requests\EjmaCalculationRequest;
use App\Models\EjmaCalculation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EjmaCalculationReport extends Mailable
{
    use Queueable, SerializesModels;

    public const DISCLAIMER = 'Preliminary calculation for quotation purposes, to be confirmed by PT Misuba Guna Indonesia engineering. Not a substitute for a certified design.';

    /** Input symbol => [label, quantity kind in EjmaCalculationRequest::QUANTITIES]. */
    private const INPUT_LABELS = [
        'design_pressure' => 'Design pressure',
        'design_temperature' => 'Design temperature',
        'required_cycles' => 'Required cycles',
        'x_comp' => 'Axial compression',
        'x_ext' => 'Axial extension',
        'lateral' => 'Lateral movement',
        'angular' => 'Angular rotation',
        'Db' => 'Bellows inside diameter Db',
        'w' => 'Convolution height w',
        't' => 'Ply thickness t',
        'n' => 'Number of plies n',
        'N' => 'Number of convolutions N',
        'q' => 'Convolution pitch q',
        'rm' => 'Mean radius rm',
        'Lt' => 'Tangent length Lt',
        'tc' => 'Collar thickness tc',
        'Lc' => 'Collar length Lc',
        'Ld' => 'Collar-to-reinforcement length Ld',
        'Ar' => 'Reinforcing member area Ar',
        'Atc' => 'Collar area Atc',
        'Zc' => 'Collar section modulus Zc',
        'ng' => 'Number of fasteners ng',
        'r' => 'Convolution radius r',
        'Dr' => 'Reinforcing member diameter Dr',
        'Dm' => 'Mean diameter Dm',
        'fw' => 'Spring rate of end connection fw',
        'Cwb' => 'Weld factor Cwb',
        'Cwc' => 'Weld factor Cwc',
        'Cwr' => 'Weld factor Cwr',
        'override_Sab' => 'Override: allowable stress Sab',
        'override_EbRt' => 'Override: modulus Eb (room temp.)',
        'override_EbDt' => 'Override: modulus Eb (design temp.)',
        'override_Sy' => 'Override: yield strength Sy',
        'override_Sym' => 'Override: yield strength Sym',
    ];

    private const COEFFICIENT_LABELS = [
        'Cp' => 'Cp (pressure stress coefficient)',
        'Cf' => 'Cf (deflection stress coefficient)',
        'Cd' => 'Cd (deflection stress coefficient)',
        'B1' => 'B1', 'B2' => 'B2', 'B3' => 'B3',
        'x' => 'x (toroidal shape parameter)',
        'Kr' => 'Kr (rounding factor)',
        'e' => 'e (equivalent axial movement per convolution), in',
        'Cr' => 'Cr', 'R' => 'R',
        'Ctheta' => 'Ctheta', 'Ktheta' => 'Ktheta', 'phi' => 'phi (rad)', 'alpha' => 'alpha',
    ];

    private const STRESS_LABELS = [
        'S1' => 'S1', 'S1p' => "S1'", 'S1pp' => "S1''", 'S2' => 'S2', 'S2p' => "S2'", 'S3' => 'S3', 'S4' => 'S4',
        'S5' => 'S5', 'S6' => 'S6', 'St' => 'St (total stress range)',
    ];

    public function __construct(public EjmaCalculation $calculation) {}

    public function envelope(): Envelope
    {
        $contact = $this->calculation->contact;

        return new Envelope(
            replyTo: [new Address($contact['email'], $contact['name'])],
            subject: $this->subjectLine(),
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.ejma-calculation', with: $this->viewData());
    }

    /** @return list<Attachment> */
    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->renderPdf(), $this->calculation->reference.'.pdf')
                ->withMime('application/pdf'),
        ];
    }

    public function subjectLine(): string
    {
        $c = $this->calculation;
        $db = rtrim(rtrim(number_format((float) ($c->inputs['metric']['Db'] ?? 0), 1, '.', ''), '0'), '.');
        $subject = "EJMA calc {$c->reference} — {$c->contact['company']} — {$db} mm {$c->bellows_type} — ".strtoupper($c->verdict);
        $failure = $c->result()->firstFailure();

        return $failure ? $subject." ({$failure->label})" : $subject;
    }

    public function pdfHtml(): string
    {
        return view('pdf.ejma-calculation', $this->viewData() + ['disclaimer' => self::DISCLAIMER])->render();
    }

    public function renderPdf(): string
    {
        // Subsetting embeds only the DejaVu glyphs used, keeping the attachment small.
        return Pdf::loadHTML($this->pdfHtml())->setPaper('a4')->setOption('isFontSubsettingEnabled', true)->output();
    }

    /** @return array<string, mixed> */
    private function viewData(): array
    {
        $c = $this->calculation;
        $result = $c->result();
        $inputs = $c->inputs;

        $rows = [];
        foreach (self::INPUT_LABELS as $field => $label) {
            if (! isset($inputs['us'][$field])) {
                continue;
            }
            $kind = EjmaCalculationRequest::QUANTITIES[$field] ?? 'ratio';
            [$mUnit, $uUnit] = EjmaCalculationRequest::UNITS[$kind] ?? ['', ''];
            $integer = in_array($field, ['n', 'N', 'ng', 'required_cycles'], true);
            $us = (float) $inputs['us'][$field] / ($kind === 'modulus' ? 1e6 : 1);
            $metric = (float) ($inputs['metric'][$field] ?? $inputs['us'][$field]);
            $entered = $inputs['entered'][$field]['value'] ?? null;
            $enteredUs = ($inputs['units'] ?? null) === 'us';
            $fmt = fn (float $v, bool $verbatim, string $unit) => (($verbatim && $entered !== null) ? (string) $entered : ($integer ? (string) round($v) : self::sig($v))).($unit !== '' ? ' '.$unit : '');
            $rows[] = [
                'label' => $label,
                'metric' => $fmt($metric, ! $enteredUs, $mUnit),
                'us' => $fmt($us, $enteredUs, $uUnit),
            ];
        }

        $repo = app(MaterialRepository::class);
        $materials = [];
        foreach (['bellows' => 'Bellows', 'collar' => 'Collar', 'reinforcement' => 'Reinforcement'] as $role => $label) {
            $id = $inputs['materials'][$role] ?? null;
            if ($id) {
                $materials[] = ['role' => $label, 'name' => $repo->find($id)?->name ?? $id];
            }
        }
        $overrides = [];
        foreach ($inputs['us'] ?? [] as $k => $v) {
            if (str_starts_with($k, 'override_')) {
                $overrides[] = self::INPUT_LABELS[$k] ?? $k;
            }
        }

        $v = $result->values;
        $isOver = fn (string $k) => isset($inputs['us']['override_'.$k]);
        $properties = [];
        foreach ([
            ['Sab', 'Bellows allowable stress Sab', 'stress'],
            ['EbRt', 'Bellows modulus Eb, room temperature', 'modulus'],
            ['EbDt', 'Bellows modulus Eb, design temperature', 'modulus'],
            ['Sy', 'Bellows yield strength Sy', 'stress'],
            ['Cm', 'Cm (forming strain factor)', 'ratio'],
            ['Cwb', 'Weld factor Cwb', 'ratio'],
            ['Sac', 'Collar allowable stress Sac', 'stress'],
            ['EcDt', 'Collar modulus Ec, design temperature', 'modulus'],
            ['Cwc', 'Weld factor Cwc', 'ratio'],
            ['Sar', 'Reinforcement allowable stress Sar', 'stress'],
            ['ErDt', 'Reinforcement modulus Er, design temperature', 'modulus'],
            ['Cwr', 'Weld factor Cwr', 'ratio'],
        ] as [$k, $label, $kind]) {
            if (! isset($v['prop_'.$k])) {
                continue;
            }
            $psi = $v['prop_'.$k];
            $properties[] = [
                'label' => $label.($isOver($k) ? ' (override)' : ''),
                'us' => match ($kind) {
                    'stress' => number_format($psi, 0).' psi',
                    'modulus' => self::sig($psi / 1e6).' × 10⁶ psi',
                    default => self::sig($psi),
                },
                'metric' => match ($kind) {
                    'stress' => self::sig($psi / Units::mpaToPsi(1.0)).' MPa',
                    'modulus' => self::sig($psi / Units::gpaToPsi(1.0)).' GPa',
                    default => self::sig($psi),
                },
            ];
        }

        $lines = array_map(fn (CheckLine $l) => $this->lineRow($l), $result->lines);

        $values = $result->values;
        $coefficients = $stresses = [];
        foreach (self::COEFFICIENT_LABELS as $k => $label) {
            if (isset($values[$k])) {
                $coefficients[] = ['label' => $label, 'value' => self::num($values[$k])];
            }
        }
        foreach (self::STRESS_LABELS as $k => $label) {
            if (isset($values[$k])) {
                $stresses[] = ['label' => $label, 'value' => self::num($values[$k]).' psi'];
            }
        }
        $loads = [];
        foreach ([
            'Ksr' => ['Spring rate per convolution-set Ksr', 'lbf/in', fn ($v) => [Units::lbfToN($v) / 25.4, 'N/mm']],
            'thrust' => ['Pressure thrust', 'lbf', fn ($v) => [Units::lbfToN($v), 'N']],
            'spring_force' => ['Spring force at maximum axial movement', 'lbf', fn ($v) => [Units::lbfToN($v), 'N']],
            'Fg' => ['Force per fastener Fg', 'lbf', fn ($v) => [Units::lbfToN($v), 'N']],
        ] as $k => [$label, $unit, $conv]) {
            if (isset($values[$k])) {
                [$m, $mUnit] = $conv($values[$k]);
                $loads[] = ['label' => $label, 'us' => self::num($values[$k]).' '.$unit, 'metric' => self::num($m).' '.$mUnit];
            }
        }

        return [
            'calculation' => $c,
            'contact' => $c->contact,
            'verdictLabel' => $c->verdictEnum()->label(),
            'verdict' => strtoupper($c->verdict),
            'date' => $c->created_at?->format('d M Y') ?? now()->format('d M Y'),
            'inputRows' => $rows,
            'materials' => $materials,
            'overrides' => $overrides,
            'properties' => $properties,
            'formingCondition' => $inputs['forming_condition'] ?? null,
            'formingMethod' => $inputs['forming_method'] ?? null,
            'coefficients' => $coefficients,
            'stresses' => $stresses,
            'loads' => $loads,
            'lines' => $lines,
            'notes' => $result->notes,
        ];
    }

    /** @return array<string, string> */
    private function lineRow(CheckLine $l): array
    {
        $isCycles = $l->key === 'fatigue' || $l->symbol === 'Nc';
        $unit = $isCycles ? ' cycles' : ' psi';
        $notEvaluated = $l->status === LineStatus::NotEvaluated;

        if ($notEvaluated && $l->value === null) {
            $value = 'Requires manufacturer fatigue data';
            $limit = $l->limit !== null && is_finite($l->limit) ? self::num($l->limit).$unit : '—';
        } elseif ($l->value !== null && ! is_finite($l->value)) {
            $value = 'No fatigue limit (St ≤ b)';
            $limit = $l->limit !== null && is_finite($l->limit) ? self::num($l->limit).$unit : '—';
        } else {
            $value = $l->value === null ? '—' : self::num($l->value).$unit;
            $limit = $l->limit === null || ! is_finite($l->limit) ? '—' : self::num($l->limit).$unit;
        }

        $margin = $l->margin();

        return [
            'symbol' => $l->symbol !== '' ? $l->symbol : $l->key,
            'label' => $l->label,
            'value' => $value,
            'limitExpression' => ($l->comparison === CheckLine::GE ? '≥ ' : '≤ ').$l->limitExpression,
            'limit' => $limit,
            'margin' => $margin === null ? '—' : number_format($margin * 100, abs($margin) >= 10 ? 0 : 1).' %',
            'status' => match ($l->status) {
                LineStatus::Pass => 'PASS',
                LineStatus::Fail => 'FAIL',
                default => 'REVIEW',
            },
            'statusKey' => $l->status->value,
            'equation' => $l->equation,
        ];
    }

    /** About 4 significant figures, trailing zeros trimmed. */
    private static function sig(float $v): string
    {
        if ($v == 0.0) {
            return '0';
        }
        $decimals = max(0, 3 - (int) floor(log10(abs($v))));
        $s = number_format($v, min($decimals, 8), '.', '');

        return str_contains($s, '.') ? rtrim(rtrim($s, '0'), '.') : $s;
    }

    private static function num(float $v): string
    {
        if (! is_finite($v)) {
            return '—';
        }
        $a = abs($v);

        return match (true) {
            $a >= 100 => number_format($v, 0),
            $a >= 10 => number_format($v, 1),
            $a >= 1 => number_format($v, 2),
            default => number_format($v, 4),
        };
    }
}
