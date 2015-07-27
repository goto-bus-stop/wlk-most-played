<?php

namespace WeLoveKpop\MostPlayed\Admin;

abstract class AdminPage
{
    /**
     * Wraps HTML in a Wordpress admin page template.
     *
     * @param string $html
     * @return string
     */
    protected function renderPage($html)
    {
        return '
            <div class="wrap">
                <h2>' . __($this->title) . '</h2>
                ' . $html . '
            </div>
        ';
    }

    /**
     * Stub.
     *
     * @return void
     */
    public function enqueue()
    {
        // nothing
    }

    /**
     * Render the page.
     *
     * @return void
     */
    public static function show()
    {
        $p = new static();
        $p->enqueue();
        echo $p->render();
    }
}
