@extends('public.layout')

@section('title', 'Flange Standards | Engineering Toolkit | PT Misuba Guna Indonesia')
@section('meta_description', 'Reference tables for DIN, JIS, and ASME flange standards.')

@section('content')
<style>
    .toolkit-page-header {
        background-color: #f8fafc;
        padding: 60px 32px 40px;
        text-align: center;
        border-bottom: 1px solid #e5e7eb;
    }

    .toolkit-page-header h1 {
        font-size: 36px;
        font-weight: 800;
        color: #111827;
        margin-bottom: 16px;
    }

    .toolkit-page-header p {
        font-size: 18px;
        color: #4b5563;
        max-width: 600px;
        margin: 0 auto;
    }

    .toolkit-container {
        max-width: 1280px;
        margin: 40px auto;
        padding: 0 32px;
    }

    /* Tabs Styling */
    .tabs-nav {
        display: flex;
        border-bottom: 2px solid #e5e7eb;
        margin-bottom: 32px;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .tab-btn {
        padding: 16px 32px;
        background: none;
        border: none;
        font-size: 16px;
        font-weight: 600;
        color: #6b7280;
        cursor: pointer;
        border-bottom: 2px solid transparent;
        margin-bottom: -2px;
        transition: all 0.2s;
        white-space: nowrap;
    }

    .tab-btn:hover {
        color: #1f2937;
    }

    .tab-btn.active {
        color: #0d9488;
        border-bottom-color: #0d9488;
    }

    .tab-pane {
        display: none;
        animation: fadeIn 0.3s ease;
    }

    .tab-pane.active {
        display: block;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(5px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Table Styling */
    .table-container {
        overflow-x: auto;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        border: 1px solid #e5e7eb;
        margin-bottom: 40px;
    }

    .standards-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 800px;
    }

    .standards-table th {
        background-color: #f8fafc;
        padding: 16px 24px;
        text-align: left;
        font-size: 14px;
        font-weight: 700;
        color: #1f2937;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-bottom: 2px solid #e5e7eb;
        position: sticky;
        top: 0;
    }

    .standards-table td {
        padding: 16px 24px;
        font-size: 15px;
        color: #4b5563;
        border-bottom: 1px solid #e5e7eb;
    }

    .standards-table tbody tr:hover {
        background-color: #f9fafb;
    }

    .table-title {
        font-size: 24px;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 16px;
        padding-left: 8px;
        border-left: 4px solid #0d9488;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #0d9488;
        font-weight: 600;
        text-decoration: none;
        margin-bottom: 32px;
        transition: color 0.2s;
    }
    
    .back-link:hover {
        color: #115e59;
    }
</style>

<div class="toolkit-page-header">
    <h1>Flange Standards</h1>
    <p>Dimensional data and bolt hole configurations for DIN, JIS, and ASME flanges.</p>
</div>

<div class="toolkit-container">
    <a href="{{ route('public.toolkit.index') }}" class="back-link">
        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Back to Toolkit
    </a>

    <!-- Search Bar -->
    <div class="search-container">
        <svg class="search-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <input type="text" id="searchInput" placeholder="Search by size, diameter, bolt holes..." onkeyup="filterTables()">
    </div>

    <!-- Tabs -->
    <div class="tabs-nav">
        <button class="tab-btn active" onclick="switchTab('tab-din', this)">DIN (EN 1092-1)</button>
        <button class="tab-btn" onclick="switchTab('tab-jis', this)">JIS (B2220)</button>
        <button class="tab-btn" onclick="switchTab('tab-asme', this)">ASME / ANSI (B16.5)</button>
    </div>

    <!-- DIN TAB -->
    <div id="tab-din" class="tab-pane active">
        <div id="din-tables"></div>
    </div>

    <!-- JIS TAB -->
    <div id="tab-jis" class="tab-pane">
        <div id="jis-tables"></div>
    </div>

    <!-- ASME TAB -->
    <div id="tab-asme" class="tab-pane">
        <div id="asme-tables"></div>
    </div>
</div>

<style>
    .search-container {
        position: relative;
        margin-bottom: 32px;
        max-width: 600px;
    }
    .search-icon {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        width: 20px;
        height: 20px;
        color: #9ca3af;
    }
    #searchInput {
        width: 100%;
        padding: 16px 16px 16px 48px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 16px;
        color: #374151;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
        font-family: inherit;
        background-color: #fff;
    }
    #searchInput:focus {
        border-color: #0d9488;
        box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.15);
    }
    .no-results {
        padding: 32px;
        text-align: center;
        color: #6b7280;
        font-size: 15px;
        background: #f9fafb;
        border-top: 1px solid #e5e7eb;
    }
    .hidden {
        display: none !important;
    }
</style>

<script>
    const flangeData = {
        din: [
            {
                title: 'DIN PN6',
                headers: ['Nominal Size (DN)', 'Outer Diameter (mm)', 'Bolt Circle (mm)', 'No. of Bolts', 'Bolt Hole (mm)', 'Thickness (mm)'],
                rows: [
                    ['DN 10', '75', '50', '4', '11', '12.2'],
                    ['DN 15', '80', '55', '4', '11', '12.4'],
                    ['DN 20', '90', '65', '4', '11', '12.5'],
                    ['DN 25', '100', '75', '4', '11', '12.6'],
                    ['DN 32', '120', '90', '4', '14', '12.8'],
                    ['DN 40', '130', '100', '4', '14', '13.0'],
                    ['DN 50', '140', '110', '4', '14', '13.2'],
                    ['DN 65', '160', '130', '4', '14', '13.6'],
                    ['DN 80', '190', '150', '4', '18', '13.9'],
                    ['DN 100', '210', '170', '4', '18', '14.4'],
                    ['DN 125', '240', '200', '8', '18', '15.0'],
                    ['DN 150', '265', '225', '8', '18', '15.6'],
                    ['DN 200', '320', '280', '8', '18', '16.8'],
                    ['DN 250', '375', '335', '12', '18', '18.0'],
                    ['DN 300', '440', '395', '12', '22', '19.2'],
                ]
            },
            {
                title: 'DIN PN10',
                headers: ['Nominal Size (DN)', 'Outer Diameter (mm)', 'Bolt Circle (mm)', 'No. of Bolts', 'Bolt Hole (mm)', 'Thickness (mm)'],
                rows: [
                    ['DN 10', '90', '60', '4', '14', '12.4'],
                    ['DN 15', '95', '65', '4', '14', '12.6'],
                    ['DN 20', '105', '75', '4', '14', '12.8'],
                    ['DN 25', '115', '85', '4', '14', '13.0'],
                    ['DN 32', '140', '100', '4', '18', '13.3'],
                    ['DN 40', '150', '110', '4', '18', '13.6'],
                    ['DN 50', '165', '125', '4', '18', '14.0'],
                    ['DN 65', '185', '145', '4', '18', '14.6'],
                    ['DN 80', '200', '160', '8', '18', '15.2'],
                    ['DN 100', '220', '180', '8', '18', '16.0'],
                    ['DN 125', '250', '210', '8', '18', '17.0'],
                    ['DN 150', '285', '240', '8', '22', '18.0'],
                    ['DN 200', '340', '295', '8', '22', '20.0'],
                    ['DN 250', '395', '350', '12', '22', '22.0'],
                    ['DN 300', '445', '400', '12', '22', '24.0'],
                    ['DN 350', '505', '460', '16', '22', '26.0'],
                    ['DN 400', '565', '515', '16', '26', '28.0'],
                    ['DN 500', '670', '620', '20', '26', '32.0'],
                    ['DN 600', '780', '725', '20', '30', '36.0'],
                ]
            },
            {
                title: 'DIN PN16',
                headers: ['Nominal Size (DN)', 'Outer Diameter (mm)', 'Bolt Circle (mm)', 'No. of Bolts', 'Bolt Hole (mm)', 'Thickness (mm)'],
                rows: [
                    ['DN 10', '90', '60', '4', '14', '12.6'],
                    ['DN 15', '95', '65', '4', '14', '13.0'],
                    ['DN 20', '105', '75', '4', '14', '13.3'],
                    ['DN 25', '115', '85', '4', '14', '13.6'],
                    ['DN 32', '140', '100', '4', '18', '14.0'],
                    ['DN 40', '150', '110', '4', '18', '14.6'],
                    ['DN 50', '165', '125', '4', '18', '15.2'],
                    ['DN 65', '185', '145', '4', '18', '16.2'],
                    ['DN 80', '200', '160', '8', '18', '17.1'],
                    ['DN 100', '220', '180', '8', '18', '18.4'],
                    ['DN 125', '250', '210', '8', '18', '20.0'],
                    ['DN 150', '285', '240', '8', '22', '21.6'],
                    ['DN 200', '340', '295', '12', '22', '24.8'],
                    ['DN 250', '405', '355', '12', '26', '28.0'],
                    ['DN 300', '460', '410', '12', '26', '31.2'],
                    ['DN 350', '520', '470', '16', '26', '34.4'],
                    ['DN 400', '580', '525', '16', '30', '37.6'],
                    ['DN 500', '715', '650', '20', '33', '44.0'],
                    ['DN 600', '840', '770', '20', '36', '50.4'],
                ]
            },
            {
                title: 'DIN PN25',
                headers: ['Nominal Size (DN)', 'Outer Diameter (mm)', 'Bolt Circle (mm)', 'No. of Bolts', 'Bolt Hole (mm)', 'Thickness (mm)'],
                rows: [
                    ['DN 10', '90', '60', '4', '14', '13.0'],
                    ['DN 15', '95', '65', '4', '14', '13.5'],
                    ['DN 20', '105', '75', '4', '14', '14.0'],
                    ['DN 25', '115', '85', '4', '14', '14.5'],
                    ['DN 32', '140', '100', '4', '18', '15.2'],
                    ['DN 40', '150', '110', '4', '18', '16.0'],
                    ['DN 50', '165', '125', '4', '18', '17.0'],
                    ['DN 65', '185', '145', '8', '18', '18.5'],
                    ['DN 80', '200', '160', '8', '18', '20.0'],
                    ['DN 100', '235', '190', '8', '22', '22.0'],
                    ['DN 125', '270', '220', '8', '26', '24.5'],
                    ['DN 150', '300', '250', '8', '26', '27.0'],
                    ['DN 200', '360', '310', '12', '26', '32.0'],
                    ['DN 250', '425', '370', '12', '30', '37.0'],
                    ['DN 300', '485', '430', '16', '30', '42.0'],
                    ['DN 350', '555', '490', '16', '33', '47.0'],
                    ['DN 400', '620', '550', '16', '36', '52.0'],
                ]
            },
            {
                title: 'DIN PN40',
                headers: ['Nominal Size (DN)', 'Outer Diameter (mm)', 'Bolt Circle (mm)', 'No. of Bolts', 'Bolt Hole (mm)', 'Thickness (mm)'],
                rows: [
                    ['DN 10', '90', '60', '4', '14', '13.6'],
                    ['DN 15', '95', '65', '4', '14', '14.4'],
                    ['DN 20', '105', '75', '4', '14', '15.2'],
                    ['DN 25', '115', '85', '4', '14', '16.0'],
                    ['DN 32', '140', '100', '4', '18', '17.1'],
                    ['DN 40', '150', '110', '4', '18', '18.4'],
                    ['DN 50', '165', '125', '4', '18', '20.0'],
                    ['DN 65', '185', '145', '8', '18', '22.4'],
                    ['DN 80', '200', '160', '8', '18', '24.8'],
                    ['DN 100', '235', '190', '8', '22', '28.0'],
                    ['DN 125', '270', '220', '8', '26', '32.0'],
                    ['DN 150', '300', '250', '8', '26', '36.0'],
                    ['DN 200', '375', '320', '12', '30', '44.0'],
                    ['DN 250', '450', '385', '12', '33', '52.0'],
                    ['DN 300', '515', '450', '16', '33', '60.0'],
                    ['DN 350', '580', '510', '16', '36', '68.0'],
                    ['DN 400', '660', '585', '16', '39', '76.0'],
                ]
            }
        ],
        jis: [
            {
                title: 'JIS 5K',
                headers: ['Nominal Size (A)', 'Nominal Size (B)', 'Outer Diameter (mm)', 'Bolt Circle (mm)', 'No. of Bolts', 'Bolt Hole (mm)', 'Thickness (mm)'],
                rows: [
                    ['10A', '3/8"', '75', '55', '4', '12', '10.2'],
                    ['15A', '1/2"', '80', '60', '4', '12', '10.3'],
                    ['20A', '3/4"', '85', '65', '4', '12', '10.4'],
                    ['25A', '1"', '95', '75', '4', '12', '10.5'],
                    ['32A', '1 1/4"', '115', '90', '4', '15', '10.6'],
                    ['40A', '1 1/2"', '120', '95', '4', '15', '10.8'],
                    ['50A', '2"', '130', '105', '4', '15', '11.0'],
                    ['65A', '2 1/2"', '155', '130', '4', '15', '11.3'],
                    ['80A', '3"', '180', '145', '4', '19', '11.6'],
                    ['100A', '4"', '200', '165', '8', '19', '12.0'],
                    ['125A', '5"', '235', '200', '8', '19', '12.5'],
                    ['150A', '6"', '265', '230', '8', '19', '13.0'],
                    ['200A', '8"', '320', '280', '8', '23', '14.0'],
                    ['250A', '10"', '385', '345', '12', '23', '15.0'],
                    ['300A', '12"', '430', '390', '12', '23', '16.0'],
                    ['350A', '14"', '480', '435', '12', '25', '17.0'],
                    ['400A', '16"', '540', '495', '16', '25', '18.0'],
                ]
            },
            {
                title: 'JIS 10K',
                headers: ['Nominal Size (A)', 'Nominal Size (B)', 'Outer Diameter (mm)', 'Bolt Circle (mm)', 'No. of Bolts', 'Bolt Hole (mm)', 'Thickness (mm)'],
                rows: [
                    ['10A', '3/8"', '90', '65', '4', '15', '10.4'],
                    ['15A', '1/2"', '95', '70', '4', '15', '10.6'],
                    ['20A', '3/4"', '100', '75', '4', '15', '10.8'],
                    ['25A', '1"', '125', '90', '4', '19', '11.0'],
                    ['32A', '1 1/4"', '135', '100', '4', '19', '11.3'],
                    ['40A', '1 1/2"', '140', '105', '4', '19', '11.6'],
                    ['50A', '2"', '155', '120', '4', '19', '12.0'],
                    ['65A', '2 1/2"', '175', '140', '4', '19', '12.6'],
                    ['80A', '3"', '185', '150', '8', '19', '13.2'],
                    ['100A', '4"', '210', '175', '8', '19', '14.0'],
                    ['125A', '5"', '250', '210', '8', '23', '15.0'],
                    ['150A', '6"', '280', '240', '8', '23', '16.0'],
                    ['200A', '8"', '330', '290', '12', '23', '18.0'],
                    ['250A', '10"', '400', '355', '12', '25', '20.0'],
                    ['300A', '12"', '445', '400', '16', '25', '22.0'],
                    ['350A', '14"', '490', '445', '16', '25', '24.0'],
                    ['400A', '16"', '560', '510', '16', '27', '26.0'],
                    ['500A', '20"', '675', '620', '20', '27', '30.0'],
                    ['600A', '24"', '795', '730', '24', '33', '34.0'],
                ]
            },
            {
                title: 'JIS 16K',
                headers: ['Nominal Size (A)', 'Nominal Size (B)', 'Outer Diameter (mm)', 'Bolt Circle (mm)', 'No. of Bolts', 'Bolt Hole (mm)', 'Thickness (mm)'],
                rows: [
                    ['10A', '3/8"', '90', '65', '4', '15', '10.6'],
                    ['15A', '1/2"', '95', '70', '4', '15', '11.0'],
                    ['20A', '3/4"', '100', '75', '4', '15', '11.3'],
                    ['25A', '1"', '125', '90', '4', '19', '11.6'],
                    ['32A', '1 1/4"', '135', '100', '4', '19', '12.0'],
                    ['40A', '1 1/2"', '140', '105', '4', '19', '12.6'],
                    ['50A', '2"', '155', '120', '8', '19', '13.2'],
                    ['65A', '2 1/2"', '175', '140', '8', '19', '14.2'],
                    ['80A', '3"', '200', '160', '8', '23', '15.1'],
                    ['100A', '4"', '225', '185', '8', '23', '16.4'],
                    ['125A', '5"', '270', '225', '8', '25', '18.0'],
                    ['150A', '6"', '305', '260', '12', '25', '19.6'],
                    ['200A', '8"', '350', '305', '12', '25', '22.8'],
                    ['250A', '10"', '430', '380', '12', '27', '26.0'],
                    ['300A', '12"', '480', '430', '16', '27', '29.2'],
                    ['350A', '14"', '540', '480', '16', '33', '32.4'],
                    ['400A', '16"', '605', '540', '16', '33', '35.6'],
                ]
            },
            {
                title: 'JIS 20K',
                headers: ['Nominal Size (A)', 'Nominal Size (B)', 'Outer Diameter (mm)', 'Bolt Circle (mm)', 'No. of Bolts', 'Bolt Hole (mm)', 'Thickness (mm)'],
                rows: [
                    ['10A', '3/8"', '90', '65', '4', '15', '10.8'],
                    ['15A', '1/2"', '95', '70', '4', '15', '11.2'],
                    ['20A', '3/4"', '100', '75', '4', '15', '11.6'],
                    ['25A', '1"', '125', '90', '4', '19', '12.0'],
                    ['32A', '1 1/4"', '135', '100', '4', '19', '12.6'],
                    ['40A', '1 1/2"', '140', '105', '4', '19', '13.2'],
                    ['50A', '2"', '155', '120', '8', '19', '14.0'],
                    ['65A', '2 1/2"', '175', '140', '8', '19', '15.2'],
                    ['80A', '3"', '200', '160', '8', '23', '16.4'],
                    ['100A', '4"', '225', '185', '8', '23', '18.0'],
                    ['125A', '5"', '270', '225', '8', '25', '20.0'],
                    ['150A', '6"', '305', '260', '12', '25', '22.0'],
                    ['200A', '8"', '350', '305', '12', '25', '26.0'],
                    ['250A', '10"', '430', '380', '12', '27', '30.0'],
                    ['300A', '12"', '480', '430', '16', '27', '34.0'],
                    ['350A', '14"', '540', '480', '16', '33', '38.0'],
                    ['400A', '16"', '605', '540', '16', '33', '42.0'],
                ]
            },
            {
                title: 'JIS 30K',
                headers: ['Nominal Size (A)', 'Nominal Size (B)', 'Outer Diameter (mm)', 'Bolt Circle (mm)', 'No. of Bolts', 'Bolt Hole (mm)', 'Thickness (mm)'],
                rows: [
                    ['10A', '3/8"', '110', '75', '4', '19', '11.2'],
                    ['15A', '1/2"', '115', '80', '4', '19', '11.8'],
                    ['20A', '3/4"', '120', '85', '4', '19', '12.4'],
                    ['25A', '1"', '130', '95', '4', '19', '13.0'],
                    ['32A', '1 1/4"', '140', '105', '4', '19', '13.8'],
                    ['40A', '1 1/2"', '160', '120', '4', '23', '14.8'],
                    ['50A', '2"', '165', '125', '8', '19', '16.0'],
                    ['65A', '2 1/2"', '200', '150', '8', '23', '17.8'],
                    ['80A', '3"', '210', '160', '8', '23', '19.6'],
                    ['100A', '4"', '240', '185', '8', '25', '22.0'],
                    ['125A', '5"', '275', '225', '8', '27', '25.0'],
                    ['150A', '6"', '325', '270', '12', '27', '28.0'],
                    ['200A', '8"', '370', '305', '12', '27', '34.0'],
                    ['250A', '10"', '450', '380', '12', '33', '40.0'],
                    ['300A', '12"', '515', '430', '16', '33', '46.0'],
                    ['350A', '14"', '560', '480', '16', '33', '52.0'],
                    ['400A', '16"', '630', '540', '16', '39', '58.0'],
                ]
            }
        ],
        asme: [
            {
                title: 'ASME Class 150',
                headers: ['Nominal Size (NPS)', 'Outer Diameter (mm)', 'Bolt Circle (mm)', 'No. of Bolts', 'Bolt Hole (mm)', 'Thickness (mm)'],
                rows: [
                    ['1/2"', '88.9', '60.5', '4', '15.7', '11.5'],
                    ['3/4"', '98.6', '69.9', '4', '15.7', '11.8'],
                    ['1"', '108.0', '79.2', '4', '15.7', '12.0'],
                    ['1 1/4"', '117.3', '88.9', '4', '15.7', '12.2'],
                    ['1 1/2"', '127.0', '98.6', '4', '15.7', '12.5'],
                    ['2"', '152.4', '120.7', '4', '19.1', '13.0'],
                    ['2 1/2"', '177.8', '139.7', '4', '19.1', '13.5'],
                    ['3"', '190.5', '152.4', '4', '19.1', '14.0'],
                    ['4"', '228.6', '190.5', '8', '19.1', '15.0'],
                    ['5"', '254.0', '215.9', '8', '22.4', '16.0'],
                    ['6"', '279.4', '241.3', '8', '22.4', '17.0'],
                    ['8"', '342.9', '298.5', '8', '22.4', '19.0'],
                    ['10"', '406.4', '362.0', '12', '25.4', '21.0'],
                    ['12"', '482.6', '431.8', '12', '25.4', '23.0'],
                    ['14"', '533.4', '476.3', '12', '28.4', '25.0'],
                    ['16"', '596.9', '539.8', '16', '28.4', '27.0'],
                    ['18"', '635.0', '577.9', '16', '31.8', '29.0'],
                    ['20"', '698.5', '635.0', '20', '31.8', '31.0'],
                    ['24"', '812.8', '749.3', '20', '35.1', '35.0'],
                ]
            },
            {
                title: 'ASME Class 300',
                headers: ['Nominal Size (NPS)', 'Outer Diameter (mm)', 'Bolt Circle (mm)', 'No. of Bolts', 'Bolt Hole (mm)', 'Thickness (mm)'],
                rows: [
                    ['1/2"', '95.3', '66.5', '4', '15.7', '12.0'],
                    ['3/4"', '117.3', '82.6', '4', '19.1', '12.5'],
                    ['1"', '124.0', '88.9', '4', '19.1', '13.0'],
                    ['1 1/4"', '133.4', '98.6', '4', '19.1', '13.5'],
                    ['1 1/2"', '155.4', '114.3', '4', '22.4', '14.0'],
                    ['2"', '165.1', '127.0', '8', '19.1', '15.0'],
                    ['2 1/2"', '190.5', '149.4', '8', '22.4', '16.0'],
                    ['3"', '209.6', '168.1', '8', '22.4', '17.0'],
                    ['4"', '254.0', '200.2', '8', '22.4', '19.0'],
                    ['5"', '279.4', '235.0', '8', '22.4', '21.0'],
                    ['6"', '317.5', '269.7', '12', '22.4', '23.0'],
                    ['8"', '381.0', '330.2', '12', '25.4', '27.0'],
                    ['10"', '444.5', '387.4', '16', '28.4', '31.0'],
                    ['12"', '520.7', '450.9', '16', '31.8', '35.0'],
                    ['14"', '584.2', '514.4', '20', '31.8', '39.0'],
                    ['16"', '647.7', '571.5', '20', '35.1', '43.0'],
                    ['18"', '711.2', '628.7', '24', '35.1', '47.0'],
                    ['20"', '774.7', '685.8', '24', '35.1', '51.0'],
                    ['24"', '914.4', '812.8', '24', '41.1', '59.0'],
                ]
            },
            {
                title: 'ASME Class 400',
                headers: ['Nominal Size (NPS)', 'Outer Diameter (mm)', 'Bolt Circle (mm)', 'No. of Bolts', 'Bolt Hole (mm)', 'Thickness (mm)'],
                rows: [
                    ['1/2"', '95.3', '66.5', '4', '15.7', '12.3'],
                    ['3/4"', '117.3', '82.6', '4', '19.1', '13.0'],
                    ['1"', '124.0', '88.9', '4', '19.1', '13.7'],
                    ['1 1/4"', '133.4', '98.6', '4', '19.1', '14.3'],
                    ['1 1/2"', '155.4', '114.3', '4', '22.4', '15.0'],
                    ['2"', '165.1', '127.0', '8', '19.1', '16.3'],
                    ['2 1/2"', '190.5', '149.4', '8', '22.4', '17.7'],
                    ['3"', '209.6', '168.1', '8', '22.4', '19.0'],
                    ['4"', '254.0', '200.2', '8', '25.4', '21.7'],
                    ['5"', '279.4', '235.0', '8', '25.4', '24.3'],
                    ['6"', '317.5', '269.7', '12', '25.4', '27.0'],
                    ['8"', '381.0', '330.2', '12', '28.4', '32.3'],
                    ['10"', '444.5', '387.4', '16', '31.8', '37.7'],
                    ['12"', '520.7', '450.9', '16', '35.1', '43.0'],
                    ['14"', '584.2', '514.4', '20', '35.1', '48.3'],
                    ['16"', '647.7', '571.5', '20', '38.1', '53.7'],
                    ['18"', '711.2', '628.7', '24', '38.1', '59.0'],
                    ['20"', '774.7', '685.8', '24', '41.1', '64.3'],
                    ['24"', '914.4', '812.8', '24', '47.8', '75.0'],
                ]
            },
            {
                title: 'ASME Class 600',
                headers: ['Nominal Size (NPS)', 'Outer Diameter (mm)', 'Bolt Circle (mm)', 'No. of Bolts', 'Bolt Hole (mm)', 'Thickness (mm)'],
                rows: [
                    ['1/2"', '95.3', '66.5', '4', '15.7', '13.0'],
                    ['3/4"', '117.3', '82.6', '4', '19.1', '14.0'],
                    ['1"', '124.0', '88.9', '4', '19.1', '15.0'],
                    ['1 1/4"', '133.4', '98.6', '4', '19.1', '16.0'],
                    ['1 1/2"', '155.4', '114.3', '4', '22.4', '17.0'],
                    ['2"', '165.1', '127.0', '8', '19.1', '19.0'],
                    ['2 1/2"', '190.5', '149.4', '8', '22.4', '21.0'],
                    ['3"', '209.6', '168.1', '8', '22.4', '23.0'],
                    ['4"', '273.1', '215.9', '8', '25.4', '27.0'],
                    ['5"', '330.2', '266.7', '8', '28.4', '31.0'],
                    ['6"', '355.6', '292.1', '12', '28.4', '35.0'],
                    ['8"', '419.1', '349.3', '12', '31.8', '43.0'],
                    ['10"', '508.0', '431.8', '16', '35.1', '51.0'],
                    ['12"', '558.8', '489.0', '20', '35.1', '59.0'],
                    ['14"', '603.3', '527.1', '20', '38.1', '67.0'],
                    ['16"', '685.8', '603.3', '20', '41.1', '75.0'],
                    ['18"', '743.0', '654.1', '20', '44.5', '83.0'],
                    ['20"', '812.8', '723.9', '24', '44.5', '91.0'],
                    ['24"', '939.8', '838.2', '24', '50.8', '107.0'],
                ]
            },
            {
                title: 'ASME Class 900',
                headers: ['Nominal Size (NPS)', 'Outer Diameter (mm)', 'Bolt Circle (mm)', 'No. of Bolts', 'Bolt Hole (mm)', 'Thickness (mm)'],
                rows: [
                    ['1/2"', '120.7', '82.6', '4', '22.4', '14.0'],
                    ['3/4"', '130.0', '88.9', '4', '22.4', '15.5'],
                    ['1"', '149.4', '101.6', '4', '25.4', '17.0'],
                    ['1 1/4"', '158.8', '111.3', '4', '25.4', '18.5'],
                    ['1 1/2"', '177.8', '124.0', '4', '28.4', '20.0'],
                    ['2"', '215.9', '165.1', '8', '25.4', '23.0'],
                    ['2 1/2"', '244.3', '190.5', '8', '28.4', '26.0'],
                    ['3"', '241.3', '190.5', '8', '25.4', '29.0'],
                    ['4"', '292.1', '235.0', '8', '31.8', '35.0'],
                    ['5"', '349.3', '279.4', '8', '35.1', '41.0'],
                    ['6"', '381.0', '317.5', '12', '31.8', '47.0'],
                    ['8"', '469.9', '393.7', '12', '38.1', '59.0'],
                    ['10"', '546.1', '469.9', '16', '38.1', '71.0'],
                    ['12"', '609.6', '533.4', '20', '38.1', '83.0'],
                    ['14"', '641.4', '558.8', '20', '41.1', '95.0'],
                    ['16"', '704.9', '616.0', '20', '44.5', '107.0'],
                    ['18"', '787.4', '685.8', '20', '50.8', '119.0'],
                    ['20"', '857.3', '749.3', '20', '54.0', '131.0'],
                    ['24"', '1041.4', '901.7', '20', '66.5', '155.0'],
                ]
            },
            {
                title: 'ASME Class 1500',
                headers: ['Nominal Size (NPS)', 'Outer Diameter (mm)', 'Bolt Circle (mm)', 'No. of Bolts', 'Bolt Hole (mm)', 'Thickness (mm)'],
                rows: [
                    ['1/2"', '120.7', '82.6', '4', '22.4', '16.0'],
                    ['3/4"', '130.0', '88.9', '4', '22.4', '18.5'],
                    ['1"', '149.4', '101.6', '4', '25.4', '21.0'],
                    ['1 1/4"', '158.8', '111.3', '4', '25.4', '23.5'],
                    ['1 1/2"', '177.8', '124.0', '4', '28.4', '26.0'],
                    ['2"', '215.9', '165.1', '8', '25.4', '31.0'],
                    ['2 1/2"', '244.3', '190.5', '8', '28.4', '36.0'],
                    ['3"', '266.7', '203.2', '8', '31.8', '41.0'],
                    ['4"', '311.2', '241.3', '8', '35.1', '51.0'],
                    ['5"', '374.7', '292.1', '8', '41.1', '61.0'],
                    ['6"', '393.7', '317.5', '12', '38.1', '71.0'],
                    ['8"', '482.6', '393.7', '12', '44.5', '91.0'],
                    ['10"', '584.2', '482.6', '12', '50.8', '111.0'],
                    ['12"', '673.1', '571.5', '16', '54.0', '131.0'],
                    ['14"', '749.3', '635.0', '16', '60.5', '151.0'],
                    ['16"', '825.5', '704.9', '16', '66.5', '171.0'],
                    ['18"', '914.4', '774.7', '16', '73.2', '191.0'],
                    ['20"', '984.3', '831.9', '16', '79.2', '211.0'],
                    ['24"', '1168.4', '990.6', '16', '92.2', '251.0'],
                ]
            },
            {
                title: 'ASME Class 2500',
                headers: ['Nominal Size (NPS)', 'Outer Diameter (mm)', 'Bolt Circle (mm)', 'No. of Bolts', 'Bolt Hole (mm)', 'Thickness (mm)'],
                rows: [
                    ['1/2"', '133.4', '88.9', '4', '22.4', '19.3'],
                    ['3/4"', '139.7', '95.3', '4', '22.4', '23.5'],
                    ['1"', '158.8', '108.0', '4', '25.4', '27.7'],
                    ['1 1/4"', '184.2', '130.0', '4', '28.4', '31.8'],
                    ['1 1/2"', '203.2', '146.1', '4', '31.8', '36.0'],
                    ['2"', '235.0', '171.5', '8', '28.4', '44.3'],
                    ['2 1/2"', '266.7', '196.9', '8', '31.8', '52.7'],
                    ['3"', '304.8', '228.6', '8', '35.1', '61.0'],
                    ['4"', '355.6', '273.1', '8', '41.1', '77.7'],
                    ['5"', '419.1', '323.9', '8', '47.8', '94.3'],
                    ['6"', '482.6', '368.3', '8', '54.0', '111.0'],
                    ['8"', '552.5', '438.2', '12', '54.0', '144.3'],
                    ['10"', '673.1', '539.8', '12', '66.5', '177.7'],
                    ['12"', '762.0', '619.3', '12', '73.2', '211.0'],
                ]
            }
        ]
    };

    function renderTables() {
        const categories = ['din', 'jis', 'asme'];
        
        categories.forEach(cat => {
            const container = document.getElementById(`${cat}-tables`);
            let html = '';
            
            flangeData[cat].forEach((table, tableIndex) => {
                html += `
                    <div class="table-section" data-table-id="${cat}-${tableIndex}">
                        <h2 class="table-title">${table.title}</h2>
                        <div class="table-container">
                            <table class="standards-table">
                                <thead>
                                    <tr>
                                        ${table.headers.map(h => `<th>${h}</th>`).join('')}
                                    </tr>
                                </thead>
                                <tbody>
                                    ${table.rows.map(row => `
                                        <tr class="data-row">
                                            ${row.map(cell => `<td>${cell}</td>`).join('')}
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                            <div class="no-results hidden">No matching sizes found in this standard.</div>
                        </div>
                    </div>
                `;
            });
            
            container.innerHTML = html;
        });
    }

    function filterTables() {
        const input = document.getElementById('searchInput').value.toLowerCase();
        
        document.querySelectorAll('.table-section').forEach(section => {
            let hasVisibleRows = false;
            const rows = section.querySelectorAll('.data-row');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(input)) {
                    row.classList.remove('hidden');
                    hasVisibleRows = true;
                } else {
                    row.classList.add('hidden');
                }
            });
            
            const noResults = section.querySelector('.no-results');
            const table = section.querySelector('table');
            
            if (hasVisibleRows) {
                noResults.classList.add('hidden');
                table.classList.remove('hidden');
                section.style.display = 'block';
            } else {
                noResults.classList.remove('hidden');
                table.classList.add('hidden');
                // Optional: hide the whole section if you only want to see sections with matches
                // section.style.display = 'none'; 
            }
        });
    }

    function switchTab(tabId, btnElement) {
        document.querySelectorAll('.tab-pane').forEach(function(pane) {
            pane.classList.remove('active');
        });
        
        document.querySelectorAll('.tab-btn').forEach(function(btn) {
            btn.classList.remove('active');
        });
        
        document.getElementById(tabId).classList.add('active');
        btnElement.classList.add('active');
    }

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', () => {
        renderTables();
    });
</script>
@endsection
