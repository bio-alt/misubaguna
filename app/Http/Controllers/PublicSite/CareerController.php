<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\PublicSite\PublicPage;
use App\Support\PublicSeo;
use Illuminate\Support\Facades\DB;

class CareerController extends Controller
{
    public function index()
    {
        $page = PublicPage::where('url_path', '/career/')->where('is_published', true)->firstOrFail();
        $siteSettings = DB::table('site_settings')->pluck('value', 'key')->toArray();
        $seo = PublicSeo::buildSeoData($page, $siteSettings);

        $openPositions = [
            [
                'id' => 'safety-officer',
                'title' => 'Safety Officer (HSE / K3)',
                'department' => 'Health, Safety & Environment',
                'type' => 'Full-Time',
                'location' => 'Tangerang & Field Sites (Banten / Jabodetabek)',
                'experience' => 'Min. 2 Years',
                'badge' => 'Urgent Requirement',
                'summary' => 'Responsible for maintaining, monitoring, and enforcing HSE/K3 safety standards across our industrial sites, warehouse facilities, and field engineering projects.',
                'responsibilities' => [
                    'Oversee health, safety, and environmental protocols across operational facilities and on-site engineering service projects.',
                    'Conduct comprehensive safety risk assessments, Job Safety Analysis (JSA), and mandatory pre-work toolbox talks.',
                    'Ensure full compliance with K3 standards, government safety regulations, and client-specific HSE requirements.',
                    'Perform regular site audits, safety inspections, hazard identification, and detailed incident reporting.',
                    'Lead safety training sessions, emergency response drills, and promote a zero-incident safety culture.'
                ],
                'requirements' => [
                    'Education: Diploma/Bachelor’s Degree in Occupational Health & Safety (K3), Environmental Engineering, or related technical field.',
                    'Certification: Certified Ahli K3 Umum (General K3 Specialist) or relevant official HSE certifications.',
                    'Experience: Minimum 2+ years of HSE/Safety Officer experience in industrial manufacturing, heavy engineering, or field services.',
                    'Knowledge: In-depth understanding of OSHA/K3 safety standards, risk mitigation, PPE management, and emergency response.',
                    'Soft Skills: Excellent communication, leadership, analytical problem-solving, and assertive enforcement of safety practices.',
                    'Mobility: Willingness to conduct regular site visits and field audits across Jabodetabek and industrial project areas.'
                ]
            ],
            [
                'id' => 'sales-engineer-pulp-paper',
                'title' => 'Sales Engineer (Pulp & Paper Machinery Product)',
                'department' => 'Sales & Technical Support',
                'type' => 'Full-Time',
                'location' => 'Tangerang / Client Sites (Indonesia)',
                'experience' => '2 - 5 Years',
                'badge' => 'Hot Opening',
                'summary' => 'Drive technical sales growth for specialized pulp and paper machinery products, expansion joints, sealing solutions, and corrosion protection linings.',
                'responsibilities' => [
                    'Promote and sell specialized pulp and paper machinery components, expansion joints, sealing systems, and protective linings.',
                    'Conduct technical sales presentations, product selection/sizing, and prepare comprehensive commercial & technical proposals.',
                    'Establish and nurture long-term strategic relationships with pulp & paper mills, plant managers, and maintenance engineers.',
                    'Perform on-site technical surveys, analyze client operational pain points, and propose tailored engineering solutions.',
                    'Collaborate with technical support, production, and field engineering teams to guarantee successful project execution and post-sales support.'
                ],
                'requirements' => [
                    'Education: Bachelor’s Degree in Mechanical Engineering, Chemical Engineering, Industrial Engineering, or related discipline.',
                    'Experience: Minimum 2-3 years of proven technical sales experience targeting Pulp & Paper mills or heavy process manufacturing industries.',
                    'Domain Knowledge: Strong understanding of pulp & paper manufacturing processes, paper machine sections, rotating equipment, and piping systems.',
                    'Skills: Outstanding technical negotiation, consultative selling, presentation, and account management skills.',
                    'Language: Proficient in Bahasa Indonesia and business English.',
                    'Mobility: Willingness to travel frequently to paper mill sites across Java, Sumatra, and other regions in Indonesia.'
                ]
            ],
            [
                'id' => 'driver-operasional',
                'title' => 'Driver (Driver Operasional & Logistik)',
                'department' => 'Logistics & Operations',
                'type' => 'Full-Time',
                'location' => 'Tangerang / Jabodetabek',
                'experience' => 'Min. 2 Years',
                'badge' => 'Active',
                'summary' => 'Safe and punctual transportation of personnel, tools, industrial materials, and equipment to operational warehouses and client project sites.',
                'responsibilities' => [
                    'Safely transport company executives, engineers, tools, and industrial products to client sites and operational facilities.',
                    'Conduct routine vehicle inspections (engine oil, tire pressure, brakes, fluid levels, cleanliness) to ensure maximum road safety.',
                    'Assist the warehouse and logistics team with loading, unloading, and verifying delivery documents (Surat Jalan).',
                    'Comply strictly with traffic laws, safe driving protocols, and company vehicle maintenance schedules.',
                    'Maintain accurate daily mileage logs, fuel receipts, and vehicle service records.'
                ],
                'requirements' => [
                    'Education: Minimum Senior High School diploma (SMA / SMK).',
                    'License: Valid SIM A / SIM B1 driving license with a clean driving record.',
                    'Experience: Minimum 2+ years of working experience as an operational driver, logistics driver, or company driver.',
                    'Knowledge: Excellent knowledge of road routes across Jabodetabek, Tangerang, and surrounding industrial estates.',
                    'Attitude: Highly disciplined, punctual, trustworthy, physically fit, and customer-service oriented.',
                    'Flexibility: Willing to work flexible hours or overtime when required for site deliveries.'
                ]
            ],
            [
                'id' => 'sales-manager',
                'title' => 'Sales Manager (Manajer Penjualan)',
                'department' => 'Sales & Business Development',
                'type' => 'Full-Time',
                'location' => 'Tangerang / Head Office',
                'experience' => 'Min. 5 Years',
                'badge' => 'Leadership Role',
                'summary' => 'Lead and scale our industrial sales team, drive revenue growth, manage key industrial accounts, and expand PT Misuba Guna Indonesia’s market presence.',
                'responsibilities' => [
                    'Formulate and execute strategic sales plans, annual revenue targets, and market penetration strategies for industrial products and engineering services.',
                    'Lead, mentor, and evaluate the performance of Sales Engineers and account representatives to achieve team targets.',
                    'Manage key corporate accounts, high-value contracts, EPC contractors, and major industrial plant clients.',
                    'Analyze market trends, competitor positioning, and customer insights to identify new business opportunities and revenue streams.',
                    'Collaborate closely with management, technical engineering, procurement, and logistics teams to deliver seamless customer experiences.'
                ],
                'requirements' => [
                    'Education: Bachelor’s Degree in Engineering, Business Administration, Marketing, or related field (Master’s degree is an advantage).',
                    'Experience: Minimum 5+ years of progressive sales experience in industrial equipment, engineering services, or heavy manufacturing, with at least 2+ years in a supervisory/managerial role.',
                    'Track Record: Proven track record of consistently meeting or exceeding high-value industrial sales targets.',
                    'Leadership: Exceptional strategic thinking, team building, contract negotiation, and senior executive communication skills.',
                    'Language: Fluent in written and spoken Bahasa Indonesia and English.',
                    'Network: Strong existing network within power generation, chemical, pulp & paper, petrochemical, or heavy industrial sectors is highly preferred.'
                ]
            ]
        ];

        $breadcrumbs = [
            ['name' => 'Home', 'url' => '/'],
            ['name' => 'Careers', 'url' => '/career/'],
        ];

        $schemas = [
            PublicSeo::organizationSchema($siteSettings),
            PublicSeo::breadcrumbSchema($breadcrumbs),
        ];

        foreach ($openPositions as $pos) {
            $schemas[] = PublicSeo::jobPostingSchema($pos, $siteSettings);
        }

        $seo['schema_json'] = $schemas;

        return view('public.pages.career', compact('page', 'seo', 'siteSettings', 'openPositions', 'breadcrumbs'));
    }
}