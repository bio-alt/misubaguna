<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ToolkitMenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_links_the_ejma_calculator_in_both_toolkit_menus(): void
    {
        $this->seed();
        // Internal links carry the trailing slash so they never bounce through the 301.
        $url = '/toolkit/ejma-bellows-calculator/';

        $this->get('/')->assertOk()
            ->assertSee('<a class="mega-link" href="'.$url.'">EJMA Bellows Calculator</a>', false)
            ->assertSee('<a class="mob-cat-link" href="'.$url.'">EJMA Bellows Calculator</a>', false)
            ->assertSeeInOrder(['Sizing & Selection', 'EJMA Bellows Calculator', 'Reference Data', 'Material Specs Library'], false)
            ->assertDontSee('href="#"', false);
    }
}
