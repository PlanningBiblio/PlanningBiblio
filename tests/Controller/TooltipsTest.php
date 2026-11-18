<?php

use Tests\PLBWebTestCase;

/**
 * initTooltips() (public/js/script.js), in a browser: a title must not disable the Bootstrap
 * component of an element (e.g. the buttons of the planning menu that open a modal).
 */
class TooltipsTest extends PLBWebTestCase
{
    public function testTitleKeepsTheBootstrapComponent(): void
    {
        $this->setUpPantherClient();
        $this->client->request('GET', '/login');

        $result = $this->client->executeScript(
            "document.body.insertAdjacentHTML('beforeend',"
            . " '<div class=\"modal\" id=\"tooltips-test-modal\" tabindex=\"-1\"><div class=\"modal-dialog\"><div class=\"modal-content\">Test</div></div></div>'"
            . " + '<button type=\"button\" id=\"tooltips-test-modal-button\" data-bs-toggle=\"modal\" data-bs-target=\"#tooltips-test-modal\" title=\"Open\">x</button>'"
            . " + '<span id=\"tooltips-test-span\" title=\"Help\">y</span>');"
            . " initTooltips();"
            . " var button = document.getElementById('tooltips-test-modal-button');"
            . " var span = document.getElementById('tooltips-test-span');"
            . " return {"
            . "   buttonToggle: button.getAttribute('data-bs-toggle'),"
            . "   buttonTooltip: bootstrap.Tooltip.getInstance(button) !== null,"
            . "   spanToggle: span.getAttribute('data-bs-toggle'),"
            . "   spanTooltip: bootstrap.Tooltip.getInstance(span) !== null"
            . " };"
        );

        $this->assertSame('modal', $result['buttonToggle'], 'the modal toggle is kept');
        $this->assertTrue($result['buttonTooltip'], 'the button has a tooltip');
        $this->assertSame('tooltip', $result['spanToggle'], 'element without toggle: tooltip');
        $this->assertTrue($result['spanTooltip'], 'the span has a tooltip');

        // The button still opens the modal
        $this->client->executeScript("document.getElementById('tooltips-test-modal-button').click();");
        $this->client->waitForVisibility('#tooltips-test-modal');
        $this->assertSelectorIsVisible('#tooltips-test-modal');
    }
}
