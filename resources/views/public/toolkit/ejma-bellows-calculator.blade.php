@extends('public.layout')

@section('title', 'EJMA Bellows Calculator | Engineering Toolkit | PT Misuba Guna Indonesia')
@section('meta_description', 'Check a metal bellows expansion joint design against EJMA Standards for unreinforced, reinforced and toroidal bellows.')

@php
    $unitSystem = old('units', 'metric') === 'us' ? 'us' : 'metric';
    $currentType = old('bellows_type', 'unreinforced');
    $unitIndex = $unitSystem === 'us' ? 1 : 0;
    $U = 'unreinforced reinforced';
    $R = 'reinforced';
    $RT = 'reinforced toroidal';
    $T = 'toroidal';
    $ALL = 'unreinforced reinforced toroidal';

    // [name, label, required, types, hint]
    $conditionFields = [
        ['design_pressure', 'Design pressure', true, $ALL, 'Internal pressure, gauge'],
        ['design_temperature', 'Design temperature', true, $ALL, null],
        ['required_cycles', 'Required cycle life', true, $ALL, 'Number of movement cycles'],
        ['x_comp', 'Axial compression x', false, $ALL, 'Total for the bellows'],
        ['x_ext', 'Axial extension x', false, $ALL, 'Total for the bellows'],
        ['lateral', 'Lateral offset y', false, $ALL, null],
        ['angular', 'Angular rotation θ', false, $ALL, null],
    ];
    $geometryFields = [
        ['Db', 'Bellows inside diameter Db', true, $ALL, null],
        ['w', 'Convolution height w', true, $U, null],
        ['q', 'Convolution pitch q', true, $ALL, null],
        ['t', 'Ply thickness t', true, $ALL, 'Nominal, one ply'],
        ['n', 'Number of plies n', true, $ALL, null],
        ['N', 'Number of convolutions N', true, $ALL, null],
        ['Lt', 'Tangent length Lt', true, $U, null],
        ['rm', 'Mean radius of convolution rm', true, $U, 'Crest and root'],
        ['r', 'Mean radius of convolution r', true, $T, null],
        ['Dm', 'Mean diameter of convolutions Dm', true, $T, null],
        ['Dr', 'Reinforcing member mean diameter Dr', true, $T, null],
        ['Ar', 'Reinforcing member area Ar', true, $RT, 'Cross-section of one member'],
        ['Ld', 'End length Ld', false, $RT, 'Weld to first convolution; optional for reinforced'],
        ['tc', 'Collar thickness tc', false, $ALL, 'Leave empty if no collar'],
        ['Lc', 'Collar length Lc', false, 'unreinforced', 'Required with a collar'],
        ['Atc', 'Collar area Atc', false, $RT, 'Cross-section of the collar'],
        ['Zc', 'Collar section modulus Zc', false, $RT, null],
        ['ng', 'Number of collar gussets ng', false, $RT, null],
    ];
    $fabricationFields = [
        ['Cwb', 'Weld efficiency Cwb (bellows)', false, $ALL, 'Longitudinal seam, default 1.0'],
        ['Cwc', 'Weld efficiency Cwc (collar)', false, $ALL, 'Default 1.0'],
        ['Cwr', 'Weld efficiency Cwr (reinforcement)', false, $RT, 'Default 1.0'],
        ['fw', 'Working spring rate fw', false, $RT, 'Per convolution; optional'],
    ];
    $advancedFields = [
        ['override_Sab', 'Allowable stress Sab', false, $ALL, 'Bellows, at design temperature'],
        ['override_EbRt', 'Elastic modulus at room temperature', false, $ALL, 'Bellows material'],
        ['override_EbDt', 'Elastic modulus at design temperature', false, $ALL, 'Bellows material'],
        ['override_Sy', 'Yield strength Sy', false, $ALL, 'Overrides the derived value'],
        ['override_Sym', 'Yield strength from test report', false, $ALL, 'Room temperature, certified'],
    ];
    $integerFields = ['required_cycles', 'n', 'N', 'ng'];
    $defaults = ['Cwb' => '1.0', 'Cwc' => '1.0', 'Cwr' => '1.0'];
    $typeLabels = [
        'unreinforced' => ['Unreinforced', 'U-shaped convolutions without rings'],
        'reinforced' => ['Reinforced', 'U-shaped convolutions with reinforcing rings'],
        'toroidal' => ['Toroidal', 'Round convolutions with reinforcing members'],
    ];
    $conditionLabels = ['annealed' => 'Annealed after forming', 'as_formed' => 'As-formed (not annealed)'];
    $methodLabels = ['mandrel_roll' => 'Expanding mandrel or roll forming', 'hydraulic' => 'Hydraulic, elastomeric or pneumatic forming'];
    $verdictStyles = ['pass' => 'is-pass', 'fail' => 'is-fail', 'review' => 'is-review'];
@endphp

@section('content')
<style>
    .ejma-hero {
        background-color: #f8fafc;
        padding: 56px 32px 32px;
        text-align: center;
        border-bottom: 1px solid #e5e7eb;
    }
    .ejma-hero h1 {
        font-size: 32px;
        font-weight: 800;
        color: #111827;
        margin: 0 0 12px;
    }
    .ejma-hero p {
        font-size: 15px;
        line-height: 1.6;
        color: #4b5563;
        max-width: 640px;
        margin: 0 auto;
    }
    .ejma-wrap {
        max-width: 1080px;
        margin: 32px auto 64px;
        padding: 0 32px;
        box-sizing: border-box;
    }
    .ejma-wrap *, .ejma-wrap *::before, .ejma-wrap *::after { box-sizing: border-box; }
    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #0d9488;
        font-weight: 600;
        font-size: 14px;
        text-decoration: none;
        margin-bottom: 24px;
    }
    .back-link:hover { color: #115e59; }

    .ejma-result {
        border: 1px solid #cbd5e1;
        border-left-width: 5px;
        border-radius: 10px;
        padding: 20px 22px;
        margin-bottom: 28px;
        background: #f8fafc;
        scroll-margin-top: 100px;
    }
    .ejma-result.is-pass { border-color: #99f6e4; border-left-color: #0d9488; background: #f0fdfa; }
    .ejma-result.is-fail { border-color: #cbd5e1; border-left-color: #0f172a; background: #f1f5f9; }
    .ejma-result.is-review { border-color: #e2e8f0; border-left-color: #64748b; background: #f8fafc; }
    .ejma-verdict {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 19px;
        font-weight: 800;
        color: #0f172a;
        margin: 0 0 8px;
    }
    .ejma-verdict svg { flex: 0 0 auto; width: 26px; height: 26px; }
    .is-pass .ejma-verdict svg { color: #0d9488; }
    .is-fail .ejma-verdict svg { color: #0f172a; }
    .is-review .ejma-verdict svg { color: #64748b; }
    .ejma-result p { font-size: 14px; color: #334155; line-height: 1.6; margin: 0 0 10px; }
    .ejma-result .ref { font-size: 15px; }
    .ejma-mail-btn {
        display: inline-block;
        margin-top: 6px;
        padding: 10px 18px;
        background: #0d9488;
        color: #fff;
        font-size: 14px;
        font-weight: 700;
        border-radius: 8px;
        text-decoration: none;
    }
    .ejma-mail-btn:hover { background: #0f766e; color: #fff; }

    .ejma-alert {
        padding: 12px 14px;
        border-radius: 8px;
        font-size: 13px;
        line-height: 1.5;
        margin-bottom: 20px;
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        border-left: 4px solid #0f172a;
        color: #0f172a;
    }
    .ejma-alert ul { margin: 6px 0 0; padding-left: 18px; }

    .ejma-section {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 20px 22px 6px;
        margin-bottom: 18px;
        min-width: 0;
    }
    .ejma-section h2 {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 16px;
        font-weight: 700;
        color: #1f2937;
        margin: 0 0 14px;
    }
    .ejma-step {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: #f0fdfa;
        color: #0d9488;
        font-size: 13px;
        font-weight: 800;
    }
    .ejma-section .intro { font-size: 13px; color: #64748b; margin: -6px 0 14px; line-height: 1.5; }

    .ejma-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 4px 16px;
    }
    .ejma-field { margin-bottom: 14px; min-width: 0; }
    .ejma-field[hidden] { display: none; }
    .ejma-field label {
        display: block;
        font-size: 12.5px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 5px;
    }
    .ejma-field label .req { color: #0d9488; }
    .ejma-input {
        display: flex;
        align-items: stretch;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background: #f8fafc;
        overflow: hidden;
    }
    .ejma-input:focus-within {
        border-color: #0d9488;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.15);
    }
    .ejma-input input, .ejma-input select {
        flex: 1 1 auto;
        min-width: 0;
        width: 100%;
        border: 0;
        background: transparent;
        padding: 9px 11px;
        font-size: 14px;
        font-family: inherit;
        color: #0f172a;
        outline: none;
    }
    .ejma-input .unit {
        flex: 0 0 auto;
        display: flex;
        align-items: center;
        padding: 0 10px;
        font-size: 12px;
        color: #64748b;
        background: #f1f5f9;
        border-left: 1px solid #e2e8f0;
        white-space: nowrap;
    }
    .ejma-input .unit:empty { display: none; }
    .ejma-field .hint { display: block; font-size: 11.5px; color: #94a3b8; margin-top: 4px; }
    .ejma-field.has-error .ejma-input { border-color: #0f172a; box-shadow: 0 0 0 1px #0f172a; }
    .ejma-field .error { display: block; font-size: 12px; font-weight: 600; color: #0f172a; margin-top: 5px; line-height: 1.45; }
    .ejma-field textarea {
        width: 100%;
        min-height: 90px;
        resize: vertical;
        border: 0;
        background: transparent;
        padding: 9px 11px;
        font-size: 14px;
        font-family: inherit;
        color: #0f172a;
        outline: none;
    }
    .span-full { grid-column: 1 / -1; }

    .ejma-choice {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 10px;
        margin-bottom: 14px;
    }
    .ejma-choice label {
        display: flex;
        gap: 10px;
        align-items: flex-start;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 11px 12px;
        cursor: pointer;
        background: #f8fafc;
    }
    .ejma-choice label:has(input:checked) { border-color: #0d9488; background: #f0fdfa; box-shadow: 0 0 0 1px #0d9488; }
    .ejma-choice input { margin-top: 3px; accent-color: #0d9488; }
    .ejma-choice strong { display: block; font-size: 14px; color: #0f172a; }
    .ejma-choice span { display: block; font-size: 12px; color: #64748b; margin-top: 2px; }

    .ejma-units {
        display: inline-flex;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        overflow: hidden;
        margin-bottom: 18px;
    }
    .ejma-units button {
        border: 0;
        background: #fff;
        padding: 8px 16px;
        font-size: 13px;
        font-weight: 700;
        color: #475569;
        cursor: pointer;
        font-family: inherit;
    }
    .ejma-units button[aria-pressed="true"] { background: #0d9488; color: #fff; }
    .ejma-toolbar { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; justify-content: space-between; }
    .ejma-toolbar .note { font-size: 12.5px; color: #64748b; margin-bottom: 18px; }

    .ejma-diagram { display: flex; flex-wrap: wrap; gap: 16px; margin: 0 0 16px; }
    .ejma-diagram figure {
        margin: 0;
        flex: 1 1 260px;
        max-width: 420px;
        border: 1px dashed #cbd5e1;
        border-radius: 8px;
        padding: 10px;
        background: #fcfdfd;
    }
    .ejma-diagram figure[hidden] { display: none; }
    .ejma-diagram svg { display: block; width: 100%; height: auto; }
    .ejma-diagram figcaption { font-size: 11.5px; color: #64748b; margin-top: 6px; }

    .ejma-advanced { border-top: 1px solid #e5e7eb; padding: 12px 0 4px; margin-top: 4px; }
    .ejma-advanced summary { cursor: pointer; font-size: 13.5px; font-weight: 700; color: #0d9488; margin-bottom: 12px; }
    .ejma-advanced p { font-size: 12.5px; color: #64748b; margin: 0 0 12px; line-height: 1.5; }

    .ejma-submit {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 14px;
        margin-top: 8px;
    }
    .ejma-submit button {
        padding: 12px 28px;
        background: #0d9488;
        color: #fff;
        font-size: 14px;
        font-weight: 700;
        border: 0;
        border-radius: 8px;
        cursor: pointer;
        font-family: inherit;
    }
    .ejma-submit button:hover { background: #0f766e; }
    .ejma-submit button:disabled { opacity: 0.7; cursor: wait; }
    .ejma-submit p { font-size: 12.5px; color: #64748b; margin: 0; flex: 1 1 280px; line-height: 1.5; }

    #calculator-form { scroll-margin-top: 100px; }
    .ejma-hp { position: absolute; left: -9999px; }

    @media (max-width: 640px) {
        .ejma-hero { padding: 40px 16px 24px; }
        .ejma-hero h1 { font-size: 26px; }
        .ejma-wrap { padding: 0 16px; margin-top: 20px; }
        .ejma-section { padding: 16px 14px 4px; }
        .ejma-grid, .ejma-choice { grid-template-columns: 1fr; }
        .ejma-submit button { width: 100%; }
    }
</style>

<div class="ejma-hero">
    <h1>EJMA Bellows Calculator</h1>
    <p>Check a metal bellows expansion joint design against the EJMA Standards: pressure stresses, fatigue life and squirm for unreinforced, reinforced and toroidal bellows. Our engineering team reviews every calculation.</p>
</div>

<div class="ejma-wrap">
    <a href="{{ route('public.toolkit.index') }}" class="back-link">
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Back to Toolkit
    </a>

    @if (is_array($result) && isset($result['reference'], $result['verdict']))
        @php $verdict = \App\Ejma\Verdict::tryFrom($result['verdict']) ?? \App\Ejma\Verdict::Review; @endphp
        <section id="result" class="ejma-result {{ $verdictStyles[$verdict->value] }}" aria-live="polite">
            <p class="ejma-verdict">
                @if ($verdict === \App\Ejma\Verdict::Pass)
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M7.5 12.5l3 3 6-6.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                @elseif ($verdict === \App\Ejma\Verdict::Fail)
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M8.5 8.5l7 7m0-7l-7 7" stroke-linecap="round"/></svg>
                @else
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 7v6m0 3.5v.5" stroke-linecap="round"/></svg>
                @endif
                <span>{{ $verdict->label() }}</span>
            </p>
            <p class="ref">Reference <strong>{{ $result['reference'] }}</strong></p>
            <p>The full calculation has been sent to our engineering team. Contact us to receive the detailed results.</p>
            <a class="ejma-mail-btn" href="mailto:{{ $contactTo }}?subject=EJMA%20calculation%20{{ rawurlencode($result['reference']) }}">Email us for the results</a>
        </section>
    @endif

    <form id="calculator-form" action="/toolkit/ejma-bellows-calculator/" method="POST" novalidate>
        @csrf
        <input type="hidden" name="units" id="ejma-units" value="{{ $unitSystem }}">
        <div class="ejma-hp" aria-hidden="true">
            <label for="ejma_website">Leave this field empty</label>
            <input type="text" id="ejma_website" name="website" tabindex="-1" autocomplete="off">
        </div>

        <div class="ejma-toolbar">
            <div class="ejma-units" role="group" aria-label="Unit system">
                <button type="button" data-units="metric" aria-pressed="{{ $unitSystem === 'metric' ? 'true' : 'false' }}">Metric</button>
                <button type="button" data-units="us" aria-pressed="{{ $unitSystem === 'us' ? 'true' : 'false' }}">US</button>
            </div>
            <span class="note">Metric: mm, bar(g), °C. US: in, psig, °F. Angles in degrees.</span>
        </div>

        @if ($errors->any())
            <div class="ejma-alert" role="alert">
                <strong>Please check the highlighted fields.</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- 1. Bellows type --}}
        <fieldset class="ejma-section">
            <h2><span class="ejma-step">1</span> Bellows type</h2>
            <div class="ejma-choice">
                @foreach ($bellowsTypes as $type)
                    <label>
                        <input type="radio" name="bellows_type" value="{{ $type->value }}" @checked($currentType === $type->value)>
                        <span><strong>{{ $typeLabels[$type->value][0] }}</strong><span>{{ $typeLabels[$type->value][1] }}</span></span>
                    </label>
                @endforeach
            </div>
            @error('bellows_type')<span class="error">{{ $message }}</span>@enderror
        </fieldset>

        @php
            $renderField = function (array $f) use ($quantities, $units, $unitIndex, $integerFields, $defaults, $errors) {
                [$name, $label, $required, $types, $hint] = $f;
                $kind = $quantities[$name] ?? 'ratio';
                $unit = $units[$kind][$unitIndex];
                $value = old($name, $defaults[$name] ?? '');
                $step = in_array($name, $integerFields, true) ? '1' : 'any';
                $id = 'ejma_'.$name;
                $html = '<div class="ejma-field'.($errors->has($name) ? ' has-error' : '').'" data-types="'.e($types).'">';
                $html .= '<label for="'.e($id).'">'.e($label).($required ? ' <span class="req">*</span>' : '').'</label>';
                $html .= '<div class="ejma-input"><input type="number" inputmode="decimal" step="'.$step.'" id="'.e($id).'" name="'.e($name).'" value="'.e($value).'"'.($errors->has($name) ? ' aria-invalid="true"' : '').'>';
                $html .= '<span class="unit" data-kind="'.e($kind).'">'.e($unit).'</span></div>';
                if ($errors->has($name)) {
                    $html .= '<span class="error">'.e($errors->first($name)).'</span>';
                } elseif ($hint) {
                    $html .= '<span class="hint">'.e($hint).'</span>';
                }

                return $html.'</div>';
            };
            $renderSelect = function (string $name, string $label, bool $required, string $types, array $options, ?string $hint = null, bool $placeholder = true) use ($errors) {
                $id = 'ejma_'.$name;
                $selected = (string) old($name, '');
                $html = '<div class="ejma-field'.($errors->has($name) ? ' has-error' : '').'" data-types="'.e($types).'">';
                $html .= '<label for="'.e($id).'">'.e($label).($required ? ' <span class="req">*</span>' : '').'</label>';
                $html .= '<div class="ejma-input"><select id="'.e($id).'" name="'.e($name).'">';
                if ($placeholder) {
                    $html .= '<option value="">Select…</option>';
                }
                foreach ($options as $value => $text) {
                    $html .= '<option value="'.e($value).'"'.((string) $value === $selected ? ' selected' : '').'>'.e($text).'</option>';
                }
                $html .= '</select></div>';
                if ($errors->has($name)) {
                    $html .= '<span class="error">'.e($errors->first($name)).'</span>';
                } elseif ($hint) {
                    $html .= '<span class="hint">'.e($hint).'</span>';
                }

                return $html.'</div>';
            };
            $materialList = fn (string $role) => collect($materialOptions[$role])->mapWithKeys(fn ($m) => [$m->id => $m->name])->all();
        @endphp

        {{-- 2. Design conditions --}}
        <fieldset class="ejma-section">
            <h2><span class="ejma-step">2</span> Design conditions</h2>
            <p class="intro">Movements are the total for the whole bellows. Leave a movement empty if it does not apply.</p>
            <div class="ejma-grid">
                @foreach ($conditionFields as $f)
                    {!! $renderField($f) !!}
                @endforeach
            </div>
        </fieldset>

        {{-- 3. Geometry --}}
        <fieldset class="ejma-section">
            <h2><span class="ejma-step">3</span> Geometry</h2>
            <div class="ejma-diagram">
                <figure data-types="{{ $U }}">
                    <svg viewBox="0 0 360 170" role="img" aria-label="U-shaped convolution dimensions">
                        <g fill="none" stroke="#0d9488" stroke-width="2.5">
                            <path d="M20 120 H70 V60 a15 15 0 0 1 30 0 V120 a15 15 0 0 0 30 0 V60 a15 15 0 0 1 30 0 V120 a15 15 0 0 0 30 0 V60 a15 15 0 0 1 30 0 V120 H310"/>
                        </g>
                        <g stroke="#64748b" stroke-width="1" fill="none">
                            <path d="M20 150 H340" stroke-dasharray="6 4"/>
                            <path d="M85 30 H145"/><path d="M85 25 V35"/><path d="M145 25 V35"/>
                            <path d="M232 45 V120"/><path d="M227 45 H237"/><path d="M227 120 H237"/>
                            <path d="M20 135 H70"/><path d="M20 130 V140"/><path d="M70 130 V140"/>
                            <path d="M85 60 L95 50"/>
                        </g>
                        <g font-family="inherit" font-size="12" fill="#334155">
                            <text x="108" y="22">q</text>
                            <text x="242" y="88">w</text>
                            <text x="38" y="132">Lt</text>
                            <text x="96" y="48">rm</text>
                            <text x="262" y="165">bellows axis (Db measured to inside)</text>
                        </g>
                    </svg>
                    <figcaption>U-shaped convolutions: pitch q, height w, mean radius rm, tangent length Lt.</figcaption>
                </figure>
                <figure data-types="{{ $T }}">
                    <svg viewBox="0 0 360 170" role="img" aria-label="Toroidal convolution dimensions">
                        <g fill="none" stroke="#0d9488" stroke-width="2.5">
                            <path d="M20 110 H60 a30 30 0 1 1 50 0 a30 30 0 1 1 50 0 a30 30 0 1 1 50 0 H320"/>
                        </g>
                        <g fill="#cbd5e1" stroke="#64748b" stroke-width="1">
                            <circle cx="85" cy="118" r="6"/><circle cx="135" cy="118" r="6"/><circle cx="185" cy="118" r="6"/>
                        </g>
                        <g stroke="#64748b" stroke-width="1" fill="none">
                            <path d="M20 150 H340" stroke-dasharray="6 4"/>
                            <path d="M85 88 L85 60"/>
                            <path d="M85 30 H135"/><path d="M85 25 V35"/><path d="M135 25 V35"/>
                        </g>
                        <g font-size="12" fill="#334155">
                            <text x="90" y="80">r</text>
                            <text x="104" y="22">q</text>
                            <text x="222" y="136">reinforcing member (Ar, Dr)</text>
                            <text x="262" y="165">bellows axis</text>
                        </g>
                    </svg>
                    <figcaption>Toroidal convolutions: radius r, pitch q, reinforcing members of area Ar on diameter Dr.</figcaption>
                </figure>
            </div>
            <div class="ejma-grid">
                @foreach ($geometryFields as $f)
                    {!! $renderField($f) !!}
                @endforeach
            </div>
        </fieldset>

        {{-- 4. Materials & fabrication --}}
        <fieldset class="ejma-section">
            <h2><span class="ejma-step">4</span> Materials &amp; fabrication</h2>
            @if (count($materialOptions['bellows']) === 0)
                <p class="intro">Material data is being prepared by our engineers. Please contact us for a design check in the meantime.</p>
            @endif
            <div class="ejma-grid">
                {!! $renderSelect('bellows_material', 'Bellows material', true, $ALL, $materialList('bellows')) !!}
                {!! $renderSelect('collar_material', 'Collar material', false, $ALL, $materialList('collar'), 'Required when a collar is given') !!}
                {!! $renderSelect('reinforcement_material', 'Reinforcement material', true, $RT, $materialList('reinforcement')) !!}
                {!! $renderSelect('forming_condition', 'Bellows condition', true, $ALL, collect($formingConditions)->mapWithKeys(fn ($c) => [$c->value => $conditionLabels[$c->value]])->all(), null, false) !!}
                {!! $renderSelect('forming_method', 'Forming method', true, $ALL, collect($formingMethods)->mapWithKeys(fn ($m) => [$m->value => $methodLabels[$m->value]])->all(), null, false) !!}
                @foreach ($fabricationFields as $f)
                    {!! $renderField($f) !!}
                @endforeach
            </div>
            <details class="ejma-advanced" @if ($errors->hasAny(['override_Sab', 'override_EbRt', 'override_EbDt', 'override_Sy', 'override_Sym'])) open @endif>
                <summary>Advanced: material property overrides</summary>
                <p>Optional. Leave empty to use our material data. Use only certified values; overrides are flagged in the engineering report.</p>
                <div class="ejma-grid">
                    @foreach ($advancedFields as $f)
                        {!! $renderField($f) !!}
                    @endforeach
                </div>
            </details>
        </fieldset>

        {{-- 5. Contact --}}
        <fieldset class="ejma-section">
            <h2><span class="ejma-step">5</span> Contact details</h2>
            <p class="intro">We send the detailed results to our engineering team, who will follow up with you.</p>
            <div class="ejma-grid">
                @foreach ([['name', 'Full name', 'text', 'name'], ['company', 'Company', 'text', 'organization'], ['email', 'Email', 'email', 'email'], ['phone', 'Phone', 'tel', 'tel']] as [$name, $label, $type, $auto])
                    <div class="ejma-field @error($name) has-error @enderror">
                        <label for="ejma_{{ $name }}">{{ $label }} <span class="req">*</span></label>
                        <div class="ejma-input"><input type="{{ $type }}" id="ejma_{{ $name }}" name="{{ $name }}" value="{{ old($name) }}" autocomplete="{{ $auto }}" required></div>
                        @error($name)<span class="error">{{ $message }}</span>@enderror
                    </div>
                @endforeach
                <div class="ejma-field span-full @error('notes') has-error @enderror">
                    <label for="ejma_notes">Notes</label>
                    <div class="ejma-input"><textarea id="ejma_notes" name="notes" placeholder="Quantity, end connections, media, delivery...">{{ old('notes') }}</textarea></div>
                    @error('notes')<span class="error">{{ $message }}</span>@enderror
                </div>
            </div>
        </fieldset>

        <div class="ejma-submit">
            <button type="submit">Check my design</button>
            <p>Preliminary check for quotation purposes, confirmed by PT Misuba Guna Indonesia engineering. Not a substitute for a certified design.</p>
        </div>
    </form>
</div>

<script>
    (function () {
        var form = document.getElementById('calculator-form');
        if (!form) return;
        var unitLabels = @json($units);
        var unitsInput = document.getElementById('ejma-units');

        function applyType() {
            var checked = form.querySelector('input[name="bellows_type"]:checked');
            var type = checked ? checked.value : 'unreinforced';
            document.querySelectorAll('[data-types]').forEach(function (el) {
                var show = el.getAttribute('data-types').split(' ').indexOf(type) !== -1;
                el.hidden = !show;
                el.querySelectorAll('input, select, textarea').forEach(function (input) { input.disabled = !show; });
            });
        }

        function applyUnits(system) {
            var index = system === 'us' ? 1 : 0;
            unitsInput.value = system;
            form.querySelectorAll('.unit[data-kind]').forEach(function (el) {
                var labels = unitLabels[el.getAttribute('data-kind')];
                el.textContent = labels ? labels[index] : '';
            });
            form.querySelectorAll('[data-units]').forEach(function (btn) {
                btn.setAttribute('aria-pressed', btn.getAttribute('data-units') === system ? 'true' : 'false');
            });
        }

        form.querySelectorAll('input[name="bellows_type"]').forEach(function (radio) {
            radio.addEventListener('change', applyType);
        });
        form.querySelectorAll('[data-units]').forEach(function (btn) {
            btn.addEventListener('click', function () { applyUnits(btn.getAttribute('data-units')); });
        });
        form.addEventListener('submit', function () {
            var button = form.querySelector('.ejma-submit button');
            button.disabled = true;
            button.textContent = 'Checking...';
        });

        applyType();
        applyUnits(unitsInput.value);
    })();
</script>
@endsection
