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
        $url = e(route('public.toolkit.ejma-calculator'));

        $this->get('/')->assertOk()
            ->assertSee('<a class="mega-link" href="'.$url.'">EJMA Bellows Calculator</a>', false)
            ->assertSee('<a class="mob-cat-link" href="'.$url.'">EJMA Bellows Calculator</a>', false)
            ->assertSeeInOrder(['Material Specs Library', 'EJMA Bellows Calculator', 'Material Specs Library', 'EJMA Bellows Calculator']);
    }
}
