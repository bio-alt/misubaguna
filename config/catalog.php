<?php

/*
| Company profile flipbook shown at /catalog/.
|
| 'pdf' is the file the viewer opens and the download buttons link to.
|
| 'sections' drive the Contents panel and the section markers on the page scrubber.
| 'page' is the page number printed in the catalog itself, which is also the flipbook
| page number: the viewer splits every landscape spread in the PDF into a left and a
| right page, so printed page 14 is flipbook page 14. When the PDF is replaced, update
| the page numbers here to match the new printed numbers. Pages past the end are ignored.
*/

return [
    'pdf' => '/wp-content/uploads/2023/11/Misuba-Guna-Indonesia-Company-Profile_Interactive.pdf',

    'title' => 'Company Profile & Product Catalog',

    'edition' => 'V.2025',

    'sections' => [
        ['group' => 'Cover',           'title' => 'Cover',                                 'page' => 1],
        ['group' => '01 Introduction', 'title' => 'Introduction & Our Strength',           'page' => 2],
        ['group' => '02 Products',     'title' => 'Pulp & Paper Solutions',                'page' => 4],
        ['group' => '02 Products',     'title' => 'Conveyor & Lifting Products',           'page' => 6],
        ['group' => '02 Products',     'title' => 'Pumps & Vacuum Pumps',                  'page' => 8],
        ['group' => '02 Products',     'title' => 'Flexible Joints',                       'page' => 10],
        ['group' => '02 Products',     'title' => 'Sealing & Corrosion Protection',        'page' => 12],
        ['group' => '02 Products',     'title' => 'Heat Transfer',                         'page' => 14],
        ['group' => '03 Latest Projects', 'title' => 'Latest Project List',                'page' => 15],
        ['group' => '03 Latest Projects', 'title' => 'Corrosion Protection & Fabrication', 'page' => 16],
        ['group' => '03 Latest Projects', 'title' => 'Material Handling & Sealing',        'page' => 18],
        ['group' => '03 Latest Projects', 'title' => 'Power Transmission & Heat Transfer', 'page' => 20],
        ['group' => '03 Latest Projects', 'title' => 'Field Projects',                     'page' => 22],
        ['group' => '03 Latest Projects', 'title' => 'PTFE Lining',                        'page' => 26],
        ['group' => '04 Clients & Contact', 'title' => 'Client List',                      'page' => 28],
        ['group' => '04 Clients & Contact', 'title' => 'Contact & Location',               'page' => 30],
    ],
];
