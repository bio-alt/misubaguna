@extends('public.layout')

@section('title', 'Scientific Calculator | Engineering Toolkit | PT Misuba Guna Indonesia')
@section('meta_description', 'Advanced online engineering and scientific calculator with trigonometric, logarithmic, memory functions, history log, and unit conversion tools.')

@section('content')
<style>
    .toolkit-page-header {
        background-color: #f8fafc;
        padding: 48px 32px 32px;
        text-align: center;
        border-bottom: 1px solid #e5e7eb;
    }

    .toolkit-page-header h1 {
        font-size: 32px;
        font-weight: 800;
        color: #111827;
        margin-bottom: 12px;
    }

    .toolkit-page-header p {
        font-size: 16px;
        color: #4b5563;
        max-width: 650px;
        margin: 0 auto;
        line-height: 1.5;
    }

    .toolkit-container {
        max-width: 1280px;
        margin: 36px auto;
        padding: 0 32px;
    }

    .breadcrumbs {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: #6b7280;
        margin-bottom: 24px;
    }

    .breadcrumbs a {
        color: #0d9488;
        text-decoration: none;
        font-weight: 600;
    }

    .breadcrumbs a:hover {
        text-decoration: underline;
    }

    /* Grid Layout */
    .calc-layout {
        display: grid;
        grid-template-columns: 1fr;
        gap: 32px;
    }

    @media (min-width: 1024px) {
        .calc-layout {
            grid-template-columns: 680px 1fr;
        }
    }

    /* Calculator Main Box */
    .calc-card {
        background: #1e293b;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2), 0 10px 10px -5px rgba(0, 0, 0, 0.1);
        border: 1px solid #334155;
        color: #f8fafc;
    }

    /* Display Screen */
    .calc-display-wrap {
        background: #0f172a;
        border: 1px solid #334155;
        border-radius: 12px;
        padding: 16px 20px;
        margin-bottom: 20px;
        text-align: right;
        min-height: 110px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.5);
    }

    .calc-status-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.05em;
        color: #64748b;
        margin-bottom: 4px;
    }

    .calc-status-badges {
        display: flex;
        gap: 8px;
    }

    .badge-unit {
        background: #0369a1;
        color: #e0f2fe;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 11px;
    }

    .badge-shift {
        background: #d97706;
        color: #fef3c7;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 11px;
        display: none;
    }

    .badge-memory {
        background: #059669;
        color: #d1fae5;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 11px;
        display: none;
    }

    .calc-expression {
        font-size: 15px;
        color: #94a3b8;
        min-height: 22px;
        word-break: break-all;
        font-family: 'Courier New', Courier, monospace;
    }

    .calc-result {
        font-size: 32px;
        font-weight: 700;
        color: #38bdf8;
        word-break: break-all;
        font-family: 'Courier New', Courier, monospace;
        letter-spacing: 0.5px;
    }

    /* Memory Row */
    .calc-mem-bar {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 8px;
        margin-bottom: 16px;
    }

    .btn-mem {
        background: #334155;
        color: #cbd5e1;
        border: none;
        padding: 8px 4px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.15s;
    }

    .btn-mem:hover {
        background: #475569;
        color: #fff;
    }

    .btn-mem:active {
        transform: scale(0.96);
    }

    /* Keypad Grid */
    .calc-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 10px;
    }

    .calc-btn {
        background: #334155;
        color: #f1f5f9;
        border: 1px solid #475569;
        border-radius: 8px;
        padding: 14px 6px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.12s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        user-select: none;
    }

    .calc-btn:hover {
        background: #475569;
        border-color: #64748b;
    }

    .calc-btn:active, .calc-btn.active-key {
        transform: scale(0.95);
        background: #64748b;
    }

    /* Button Colors */
    .btn-fn {
        background: #1e293b;
        color: #38bdf8;
        border-color: #334155;
    }
    .btn-fn:hover {
        background: #334155;
        color: #7dd3fc;
    }

    .btn-op {
        background: #0284c7;
        color: #fff;
        border-color: #0369a1;
        font-weight: 700;
        font-size: 18px;
    }
    .btn-op:hover {
        background: #0369a1;
    }

    .btn-equals {
        background: #0d9488;
        color: #fff;
        border-color: #0f766e;
        font-weight: 800;
        font-size: 20px;
    }
    .btn-equals:hover {
        background: #0f766e;
    }

    .btn-clear {
        background: #dc2626;
        color: #fff;
        border-color: #b91c1c;
        font-weight: 700;
    }
    .btn-clear:hover {
        background: #b91c1c;
    }

    .btn-toggle {
        background: #475569;
        color: #fde047;
        font-weight: 700;
    }
    .btn-toggle.active {
        background: #ca8a04;
        color: #fff;
    }

    .btn-num {
        background: #0f172a;
        color: #f8fafc;
        font-size: 18px;
        font-weight: 700;
        border-color: #1e293b;
    }
    .btn-num:hover {
        background: #1e293b;
    }

    /* Right Column: History & Engineering Converters */
    .side-panel {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    .panel-card {
        background: #fff;
        border-radius: 12px;
        padding: 24px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }

    .panel-card h3 {
        font-size: 18px;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .panel-card h3 svg {
        width: 20px;
        height: 20px;
        color: #0d9488;
    }

    /* History List */
    .history-list {
        max-height: 220px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .history-item {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 10px 12px;
        cursor: pointer;
        transition: all 0.15s;
    }

    .history-item:hover {
        background: #f0fdfa;
        border-color: #99f6e4;
    }

    .history-expr {
        font-size: 12px;
        color: #64748b;
        font-family: monospace;
    }

    .history-res {
        font-size: 15px;
        font-weight: 700;
        color: #0f766e;
        font-family: monospace;
    }

    .btn-clear-hist {
        background: none;
        border: none;
        color: #ef4444;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
    }
    .btn-clear-hist:hover {
        text-decoration: underline;
    }

    /* Converters Styling */
    .converter-tabs {
        display: flex;
        gap: 6px;
        border-bottom: 1px solid #e5e7eb;
        padding-bottom: 8px;
        margin-bottom: 16px;
    }

    .conv-tab-btn {
        background: none;
        border: none;
        padding: 6px 12px;
        font-size: 13px;
        font-weight: 600;
        color: #6b7280;
        cursor: pointer;
        border-radius: 6px;
    }

    .conv-tab-btn.active {
        background: #f0fdfa;
        color: #0d9488;
    }

    .conv-pane {
        display: none;
    }
    .conv-pane.active {
        display: block;
    }

    .conv-group {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-bottom: 12px;
    }

    .conv-field label {
        display: block;
        font-size: 12px;
        font-weight: 700;
        color: #4b5563;
        margin-bottom: 4px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .conv-field input, .conv-field select {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 600;
        color: #1f2937;
        outline: none;
    }

    .conv-field input:focus, .conv-field select:focus {
        border-color: #0d9488;
        box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.15);
    }
</style>

<!-- Load Math.js CDN for robust calculation parsing -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/mathjs/12.4.1/math.js"></script>

<div class="toolkit-page-header">
    <h1>Scientific Calculator</h1>
    <p>Perform complex engineering, mathematical, trigonometric, and logarithmic calculations with ease. Includes memory storage, evaluation history, and quick unit converters.</p>
</div>

<div class="toolkit-container">
    <div class="breadcrumbs">
        <a href="{{ route('public.home') }}">Home</a>
        <span>/</span>
        <a href="{{ route('public.toolkit.index') }}">Engineering Toolkit</a>
        <span>/</span>
        <span>Scientific Calculator</span>
    </div>

    <div class="calc-layout">
        <!-- LEFT: Calculator Card -->
        <div class="calc-card">
            <!-- Screen -->
            <div class="calc-display-wrap">
                <div class="calc-status-bar">
                    <span id="calc-mode-label">SCIENTIFIC</span>
                    <div class="calc-status-badges">
                        <span class="badge-shift" id="badge-shift">2ND</span>
                        <span class="badge-memory" id="badge-mem">M</span>
                        <span class="badge-unit" id="badge-unit">DEG</span>
                    </div>
                </div>
                <div class="calc-expression" id="calc-expression"></div>
                <div class="calc-result" id="calc-result">0</div>
            </div>

            <!-- Memory Bar -->
            <div class="calc-mem-bar">
                <button class="btn-mem" onclick="calcMemClear()">MC</button>
                <button class="btn-mem" onclick="calcMemRecall()">MR</button>
                <button class="btn-mem" onclick="calcMemAdd()">M+</button>
                <button class="btn-mem" onclick="calcMemSub()">M-</button>
                <button class="btn-mem" onclick="calcMemStore()">MS</button>
            </div>

            <!-- Keypad Grid -->
            <div class="calc-grid">
                <!-- Row 1 -->
                <button class="calc-btn btn-toggle" id="btn-shift" onclick="toggleShift()">2nd</button>
                <button class="calc-btn btn-toggle" id="btn-angle" onclick="toggleAngleUnit()">DEG</button>
                <button class="calc-btn btn-fn" onclick="insertFunc('sin')"><span class="lbl-primary">sin</span></button>
                <button class="calc-btn btn-fn" onclick="insertFunc('cos')"><span class="lbl-primary">cos</span></button>
                <button class="calc-btn btn-fn" onclick="insertFunc('tan')"><span class="lbl-primary">tan</span></button>

                <!-- Row 2 -->
                <button class="calc-btn btn-fn" onclick="insertOp('^2')"><span class="lbl-primary">x²</span></button>
                <button class="calc-btn btn-fn" onclick="insertOp('^')"><span class="lbl-primary">xʸ</span></button>
                <button class="calc-btn btn-fn" onclick="insertFunc('sqrt')"><span class="lbl-primary">√</span></button>
                <button class="calc-btn btn-fn" onclick="insertOp('^(1/')"><span class="lbl-primary">ⁿ√</span></button>
                <button class="calc-btn btn-fn" onclick="insertFunc('10^')"><span class="lbl-primary">10ˣ</span></button>

                <!-- Row 3 -->
                <button class="calc-btn btn-fn" onclick="insertFunc('log')"><span class="lbl-primary">log</span></button>
                <button class="calc-btn btn-fn" onclick="insertFunc('ln')"><span class="lbl-primary">ln</span></button>
                <button class="calc-btn btn-fn" onclick="insertConst('pi')">π</button>
                <button class="calc-btn btn-fn" onclick="insertConst('e')">e</button>
                <button class="calc-btn btn-clear" onclick="calcClearAll()">AC</button>

                <!-- Row 4 -->
                <button class="calc-btn btn-fn" onclick="insertFunc('1/')">1/x</button>
                <button class="calc-btn btn-fn" onclick="insertFunc('abs')">|x|</button>
                <button class="calc-btn btn-fn" onclick="insertOp('!')">x!</button>
                <button class="calc-btn btn-fn" onclick="insertOp('%')">%</button>
                <button class="calc-btn btn-clear" onclick="calcBackspace()">⌫</button>

                <!-- Row 5 -->
                <button class="calc-btn btn-fn" onclick="insertChar('(')">(</button>
                <button class="calc-btn btn-fn" onclick="insertChar(')')">)</button>
                <button class="calc-btn btn-num" onclick="insertChar('7')">7</button>
                <button class="calc-btn btn-num" onclick="insertChar('8')">8</button>
                <button class="calc-btn btn-num" onclick="insertChar('9')">9</button>

                <!-- Row 6 -->
                <button class="calc-btn btn-fn" onclick="insertOp(' mod ')">mod</button>
                <button class="calc-btn btn-op" onclick="insertOp('/')">÷</button>
                <button class="calc-btn btn-num" onclick="insertChar('4')">4</button>
                <button class="calc-btn btn-num" onclick="insertChar('5')">5</button>
                <button class="calc-btn btn-num" onclick="insertChar('6')">6</button>

                <!-- Row 7 -->
                <button class="calc-btn btn-fn" onclick="insertChar('e')">EXP</button>
                <button class="calc-btn btn-op" onclick="insertOp('*')">×</button>
                <button class="calc-btn btn-num" onclick="insertChar('1')">1</button>
                <button class="calc-btn btn-num" onclick="insertChar('2')">2</button>
                <button class="calc-btn btn-num" onclick="insertChar('3')">3</button>

                <!-- Row 8 -->
                <button class="calc-btn btn-fn" onclick="toggleSign()">±</button>
                <button class="calc-btn btn-op" onclick="insertOp('-')">−</button>
                <button class="calc-btn btn-num" onclick="insertChar('0')">0</button>
                <button class="calc-btn btn-num" onclick="insertChar('.')">.</button>
                <button class="calc-btn btn-op" onclick="insertOp('+')">+</button>

                <!-- Row 9 -->
                <button class="calc-btn btn-equals" style="grid-column: span 5;" onclick="calcEvaluate()">=</button>
            </div>
        </div>

        <!-- RIGHT: Side Panel (History & Converters) -->
        <div class="side-panel">
            <!-- Calculation History Card -->
            <div class="panel-card">
                <h3>
                    Calculation History
                    <button class="btn-clear-hist" onclick="clearHistory()">Clear</button>
                </h3>
                <div class="history-list" id="history-list">
                    <div style="color: #94a3b8; font-size: 13px; font-style: italic; text-align: center; padding: 20px 0;" id="empty-hist-msg">
                        No previous calculations
                    </div>
                </div>
            </div>

            <!-- Quick Engineering Converters Card -->
            <div class="panel-card">
                <h3>
                    Engineering Converters
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                </h3>

                <div class="converter-tabs">
                    <button class="conv-tab-btn active" onclick="switchConvTab(this, 'conv-press')">Pressure</button>
                    <button class="conv-tab-btn" onclick="switchConvTab(this, 'conv-temp')">Temperature</button>
                    <button class="conv-tab-btn" onclick="switchConvTab(this, 'conv-len')">Length/Dim</button>
                </div>

                <!-- Pressure Converter -->
                <div class="conv-pane active" id="conv-press">
                    <div class="conv-group">
                        <div class="conv-field">
                            <label>From Value</label>
                            <input type="number" id="press-val-in" value="1" oninput="convertPressure()">
                        </div>
                        <div class="conv-field">
                            <label>From Unit</label>
                            <select id="press-unit-in" onchange="convertPressure()">
                                <option value="bar">Bar</option>
                                <option value="psi">PSI (lb/in²)</option>
                                <option value="pa">Pascal (Pa)</option>
                                <option value="kpa">Kilopascal (kPa)</option>
                                <option value="mpa">Megapascal (MPa)</option>
                                <option value="atm">Atmosphere (atm)</option>
                                <option value="kgcm2">kgf/cm²</option>
                            </select>
                        </div>
                    </div>
                    <div class="conv-group">
                        <div class="conv-field">
                            <label>Result Value</label>
                            <input type="text" id="press-val-out" readonly style="background:#f8fafc; font-weight:700; color:#0d9488;">
                        </div>
                        <div class="conv-field">
                            <label>To Unit</label>
                            <select id="press-unit-out" onchange="convertPressure()">
                                <option value="psi">PSI (lb/in²)</option>
                                <option value="bar">Bar</option>
                                <option value="pa">Pascal (Pa)</option>
                                <option value="kpa">Kilopascal (kPa)</option>
                                <option value="mpa">Megapascal (MPa)</option>
                                <option value="atm">Atmosphere (atm)</option>
                                <option value="kgcm2">kgf/cm²</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Temperature Converter -->
                <div class="conv-pane" id="conv-temp">
                    <div class="conv-group">
                        <div class="conv-field">
                            <label>From Value</label>
                            <input type="number" id="temp-val-in" value="100" oninput="convertTemp()">
                        </div>
                        <div class="conv-field">
                            <label>From Unit</label>
                            <select id="temp-unit-in" onchange="convertTemp()">
                                <option value="c">Celsius (°C)</option>
                                <option value="f">Fahrenheit (°F)</option>
                                <option value="k">Kelvin (K)</option>
                            </select>
                        </div>
                    </div>
                    <div class="conv-group">
                        <div class="conv-field">
                            <label>Result Value</label>
                            <input type="text" id="temp-val-out" readonly style="background:#f8fafc; font-weight:700; color:#0d9488;">
                        </div>
                        <div class="conv-field">
                            <label>To Unit</label>
                            <select id="temp-unit-out" onchange="convertTemp()">
                                <option value="f">Fahrenheit (°F)</option>
                                <option value="c">Celsius (°C)</option>
                                <option value="k">Kelvin (K)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Length / Dimension Converter -->
                <div class="conv-pane" id="conv-len">
                    <div class="conv-group">
                        <div class="conv-field">
                            <label>From Value</label>
                            <input type="number" id="len-val-in" value="1" oninput="convertLength()">
                        </div>
                        <div class="conv-field">
                            <label>From Unit</label>
                            <select id="len-unit-in" onchange="convertLength()">
                                <option value="inch">Inch (in)</option>
                                <option value="mm">Millimeter (mm)</option>
                                <option value="cm">Centimeter (cm)</option>
                                <option value="m">Meter (m)</option>
                                <option value="ft">Feet (ft)</option>
                            </select>
                        </div>
                    </div>
                    <div class="conv-group">
                        <div class="conv-field">
                            <label>Result Value</label>
                            <input type="text" id="len-val-out" readonly style="background:#f8fafc; font-weight:700; color:#0d9488;">
                        </div>
                        <div class="conv-field">
                            <label>To Unit</label>
                            <select id="len-unit-out" onchange="convertLength()">
                                <option value="mm">Millimeter (mm)</option>
                                <option value="inch">Inch (in)</option>
                                <option value="cm">Centimeter (cm)</option>
                                <option value="m">Meter (m)</option>
                                <option value="ft">Feet (ft)</option>
                            </select>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    // Calculator State Variables
    let currentExpr = '';
    let currentRes = '0';
    let isEvaluated = false;
    let angleUnit = 'DEG'; // DEG or RAD
    let isShift = false;
    let memoryValue = 0;
    let historyStack = [];

    // Load History from LocalStorage
    document.addEventListener('DOMContentLoaded', () => {
        const savedHist = localStorage.getItem('misuba_calc_history');
        if (savedHist) {
            try {
                historyStack = JSON.parse(savedHist);
                renderHistory();
            } catch (e) {}
        }
        convertPressure();
        convertTemp();
        convertLength();
    });

    // Display updates
    function updateDisplay() {
        document.getElementById('calc-expression').innerText = currentExpr;
        document.getElementById('calc-result').innerText = currentRes;

        const memBadge = document.getElementById('badge-mem');
        memBadge.style.display = memoryValue !== 0 ? 'inline-block' : 'none';

        const shiftBadge = document.getElementById('badge-shift');
        shiftBadge.style.display = isShift ? 'inline-block' : 'none';
        
        document.getElementById('badge-unit').innerText = angleUnit;
    }

    // Insert Character
    function insertChar(char) {
        if (isEvaluated) {
            // If starting fresh after result, clear unless inserting an operator
            if (!'+-*/^%'.includes(char)) {
                currentExpr = '';
            } else {
                currentExpr = currentRes;
            }
            isEvaluated = false;
        }
        currentExpr += char;
        updateDisplay();
    }

    // Insert Operator
    function insertOp(op) {
        if (isEvaluated) {
            currentExpr = currentRes;
            isEvaluated = false;
        }
        if (currentExpr === '' && currentRes !== '0') {
            currentExpr = currentRes;
        }
        currentExpr += op;
        updateDisplay();
    }

    // Insert Function
    function insertFunc(fnName) {
        if (isEvaluated) {
            currentExpr = '';
            isEvaluated = false;
        }
        if (isShift) {
            if (fnName === 'sin') fnName = 'asin';
            else if (fnName === 'cos') fnName = 'acos';
            else if (fnName === 'tan') fnName = 'atan';
            else if (fnName === 'sqrt') fnName = 'cbrt';
            else if (fnName === '10^') fnName = '2^';
            else if (fnName === 'log') fnName = 'log2';
        }

        if (fnName === '1/') {
            currentExpr += '1/(';
        } else if (fnName === '10^' || fnName === '2^') {
            currentExpr += fnName;
        } else {
            currentExpr += fnName + '(';
        }
        updateDisplay();
    }

    // Insert Constant
    function insertConst(c) {
        if (isEvaluated) {
            currentExpr = '';
            isEvaluated = false;
        }
        if (c === 'pi') currentExpr += 'pi';
        else if (c === 'e') currentExpr += 'e';
        updateDisplay();
    }

    // Toggle Angle Unit (DEG <-> RAD)
    function toggleAngleUnit() {
        angleUnit = angleUnit === 'DEG' ? 'RAD' : 'DEG';
        document.getElementById('btn-angle').innerText = angleUnit;
        document.getElementById('badge-unit').innerText = angleUnit;
    }

    // Toggle Shift / 2nd Function
    function toggleShift() {
        isShift = !isShift;
        const btn = document.getElementById('btn-shift');
        if (isShift) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
        updateDisplay();
    }

    // Sign flip (±)
    function toggleSign() {
        if (currentExpr.startsWith('-')) {
            currentExpr = currentExpr.slice(1);
        } else if (currentExpr !== '') {
            currentExpr = '-(' + currentExpr + ')';
        } else if (currentRes !== '0') {
            currentRes = (parseFloat(currentRes) * -1).toString();
        }
        updateDisplay();
    }

    // Clear All
    function calcClearAll() {
        currentExpr = '';
        currentRes = '0';
        isEvaluated = false;
        updateDisplay();
    }

    // Backspace
    function calcBackspace() {
        if (isEvaluated) {
            currentExpr = '';
            isEvaluated = false;
        } else {
            currentExpr = currentExpr.slice(0, -1);
        }
        updateDisplay();
    }

    // Memory Operations
    function calcMemClear() {
        memoryValue = 0;
        updateDisplay();
    }

    function calcMemRecall() {
        insertChar(memoryValue.toString());
    }

    function calcMemStore() {
        try {
            const val = parseFloat(currentRes !== '0' ? currentRes : currentExpr);
            if (!isNaN(val)) memoryValue = val;
        } catch (e) {}
        updateDisplay();
    }

    function calcMemAdd() {
        try {
            const val = parseFloat(currentRes !== '0' ? currentRes : currentExpr);
            if (!isNaN(val)) memoryValue += val;
        } catch (e) {}
        updateDisplay();
    }

    function calcMemSub() {
        try {
            const val = parseFloat(currentRes !== '0' ? currentRes : currentExpr);
            if (!isNaN(val)) memoryValue -= val;
        } catch (e) {}
        updateDisplay();
    }

    // Calculation Evaluation Engine
    function calcEvaluate() {
        if (!currentExpr && currentRes === '0') return;
        const rawExpr = currentExpr || currentRes;

        try {
            let processedExpr = rawExpr;

            // Handle Math.js degree mode trigonometric conversions if DEG mode active
            if (angleUnit === 'DEG') {
                // Replace sin(x) with sin(x deg), cos(x) with cos(x deg), tan(x) with tan(x deg)
                // regex matching trig calls that aren't already followed by deg
                processedExpr = processedExpr.replace(/sin\(([^)]+)\)/g, (m, arg) => `sin(${arg} deg)`);
                processedExpr = processedExpr.replace(/cos\(([^)]+)\)/g, (m, arg) => `cos(${arg} deg)`);
                processedExpr = processedExpr.replace(/tan\(([^)]+)\)/g, (m, arg) => `tan(${arg} deg)`);
            }

            let result;
            if (typeof math !== 'undefined') {
                result = math.evaluate(processedExpr);
                // If result is unit or object, get value
                if (result && typeof result.toNumber === 'function') {
                    result = result.toNumber();
                } else if (result && typeof result.value === 'number') {
                    result = result.value;
                }
            } else {
                // Pure JS Fallback Evaluator
                result = evalFallback(rawExpr);
            }

            // Format float precision
            if (typeof result === 'number') {
                if (Math.abs(result) < 1e-12 && result !== 0) result = 0;
                // Avoid js precision float errors (e.g. 0.1 + 0.2 = 0.30000000000000004)
                result = Number(Math.round(result + 'e12') + 'e-12');
            }

            currentRes = result.toString();
            isEvaluated = true;

            // Add to history
            addHistory(rawExpr, currentRes);

        } catch (err) {
            console.error(err);
            currentRes = 'Error';
            isEvaluated = true;
        }
        updateDisplay();
    }

    // Pure JS Fallback
    function evalFallback(expr) {
        let e = expr.replace(/×/g, '*').replace(/÷/g, '/');
        e = e.replace(/pi/g, 'Math.PI').replace(/\be\b/g, 'Math.E');
        e = e.replace(/sqrt\(/g, 'Math.sqrt(');
        e = e.replace(/cbrt\(/g, 'Math.cbrt(');
        e = e.replace(/log\(/g, 'Math.log10(');
        e = e.replace(/ln\(/g, 'Math.log(');
        e = e.replace(/abs\(/g, 'Math.abs(');
        
        if (angleUnit === 'DEG') {
            e = e.replace(/sin\(([^)]+)\)/g, 'Math.sin(($1)*Math.PI/180)');
            e = e.replace(/cos\(([^)]+)\)/g, 'Math.cos(($1)*Math.PI/180)');
            e = e.replace(/tan\(([^)]+)\)/g, 'Math.tan(($1)*Math.PI/180)');
            e = e.replace(/asin\(([^)]+)\)/g, '(Math.asin($1)*180/Math.PI)');
            e = e.replace(/acos\(([^)]+)\)/g, '(Math.acos($1)*180/Math.PI)');
            e = e.replace(/atan\(([^)]+)\)/g, '(Math.atan($1)*180/Math.PI)');
        } else {
            e = e.replace(/sin\(/g, 'Math.sin(');
            e = e.replace(/cos\(/g, 'Math.cos(');
            e = e.replace(/tan\(/g, 'Math.tan(');
            e = e.replace(/asin\(/g, 'Math.asin(');
            e = e.replace(/acos\(/g, 'Math.acos(');
            e = e.replace(/atan\(/g, 'Math.atan(');
        }

        return Function('"use strict";return (' + e + ')')();
    }

    // History Functions
    function addHistory(expr, res) {
        historyStack.unshift({ expr, res });
        if (historyStack.length > 20) historyStack.pop();
        localStorage.setItem('misuba_calc_history', JSON.stringify(historyStack));
        renderHistory();
    }

    function renderHistory() {
        const listEl = document.getElementById('history-list');
        if (historyStack.length === 0) {
            listEl.innerHTML = '<div style="color: #94a3b8; font-size: 13px; font-style: italic; text-align: center; padding: 20px 0;">No previous calculations</div>';
            return;
        }
        listEl.innerHTML = historyStack.map((item, idx) => `
            <div class="history-item" onclick="loadHistoryItem(${idx})">
                <div class="history-expr">${item.expr}</div>
                <div class="history-res">= ${item.res}</div>
            </div>
        `).join('');
    }

    function loadHistoryItem(idx) {
        const item = historyStack[idx];
        if (item) {
            currentExpr = item.expr;
            currentRes = item.res;
            isEvaluated = true;
            updateDisplay();
        }
    }

    function clearHistory() {
        historyStack = [];
        localStorage.removeItem('misuba_calc_history');
        renderHistory();
    }

    // Keyboard support
    document.addEventListener('keydown', (e) => {
        // Prevent shortcuts if focus is inside input elements
        if (['INPUT', 'SELECT', 'TEXTAREA'].includes(document.activeElement.tagName)) return;

        if (e.key >= '0' && e.key <= '9') insertChar(e.key);
        else if (e.key === '.') insertChar('.');
        else if (e.key === '+') insertOp('+');
        else if (e.key === '-') insertOp('-');
        else if (e.key === '*') insertOp('*');
        else if (e.key === '/') insertOp('/');
        else if (e.key === '^') insertOp('^');
        else if (e.key === '(') insertChar('(');
        else if (e.key === ')') insertChar(')');
        else if (e.key === 'Enter' || e.key === '=') {
            e.preventDefault();
            calcEvaluate();
        } else if (e.key === 'Backspace') {
            e.preventDefault();
            calcBackspace();
        } else if (e.key === 'Escape') {
            calcClearAll();
        }
    });

    // Converters Functionality
    function switchConvTab(btn, paneId) {
        document.querySelectorAll('.conv-tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.conv-pane').forEach(p => p.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById(paneId).classList.add('active');
    }

    // Pressure Conversion logic
    function convertPressure() {
        const val = parseFloat(document.getElementById('press-val-in').value) || 0;
        const from = document.getElementById('press-unit-in').value;
        const to = document.getElementById('press-unit-out').value;

        // Convert to base Pascals
        const toPa = {
            pa: 1,
            kpa: 1000,
            mpa: 1000000,
            bar: 100000,
            psi: 6894.757,
            atm: 101325,
            kgcm2: 98066.5
        };

        const paVal = val * toPa[from];
        const resVal = paVal / toPa[to];

        document.getElementById('press-val-out').value = resVal >= 10000 || resVal < 0.001 && resVal > 0 ? resVal.toExponential(4) : resVal.toFixed(4).replace(/\.?0+$/, '');
    }

    // Temperature Conversion logic
    function convertTemp() {
        const val = parseFloat(document.getElementById('temp-val-in').value) || 0;
        const from = document.getElementById('temp-unit-in').value;
        const to = document.getElementById('temp-unit-out').value;

        let celsius = val;
        if (from === 'f') celsius = (val - 32) * (5/9);
        else if (from === 'k') celsius = val - 273.15;

        let resVal = celsius;
        if (to === 'f') resVal = (celsius * 9/5) + 32;
        else if (to === 'k') resVal = celsius + 273.15;

        document.getElementById('temp-val-out').value = resVal.toFixed(2).replace(/\.?0+$/, '');
    }

    // Length Conversion logic
    function convertLength() {
        const val = parseFloat(document.getElementById('len-val-in').value) || 0;
        const from = document.getElementById('len-unit-in').value;
        const to = document.getElementById('len-unit-out').value;

        // Base millimeters
        const toMm = {
            mm: 1,
            cm: 10,
            m: 1000,
            inch: 25.4,
            ft: 304.8
        };

        const mmVal = val * toMm[from];
        const resVal = mmVal / toMm[to];

        document.getElementById('len-val-out').value = resVal.toFixed(4).replace(/\.?0+$/, '');
    }
</script>
@endsection
