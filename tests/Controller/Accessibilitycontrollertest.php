<?php

use App\Entity\Agent;
use App\Entity\Config;
use Doctrine\ORM\EntityManagerInterface;
use Tests\FixtureBuilder;
use Tests\PLBWebTestCase;

class AccessibilityControllerTest extends PLBWebTestCase
{
    public static function setUpBeforeClass(): void
    {
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
    }

    public static function tearDownAfterClass(): void
    {
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $a11yContact = $entityManager->getRepository(Config::class)->findOneBy(['nom' => 'a11yContact']);
        $a11yMultiannualPlan = $entityManager->getRepository(Config::class)->findOneBy(['nom' => 'a11yMultiannualPlan']);
        $a11yEntityName = $entityManager->getRepository(Config::class)->findOneBy(['nom' => 'a11yEntityName']);

        $a11yContact->setValue('[a11yContact]');
        $a11yMultiannualPlan->setValue('');
        $a11yEntityName->setValue('[a11yEntityName]');

        $entityManager->persist($a11yContact);
        $entityManager->persist($a11yMultiannualPlan);
        $entityManager->persist($a11yEntityName);
        $entityManager->flush();
    }

    public function testAccessibility(): void
    {
        $builder = new FixtureBuilder();

        $agent = $builder->build(Agent::class, array('login' => 'jdevoe'));

        $this->logInAgent($agent, [99, 100]);

        $client = static::createClient();

        $crawler = $client->request('GET', '/accessibility');

        $this->assertEquals(
            200,
            $client->getResponse()->getStatusCode(),
            'accessibility page returns 200'
        );

        $page = $crawler->filterXPath('//main')->text(null, false);

        // The seven sections mandated by the Order of 20 September 2019
        $sections = array(
            'Engagement',
            'État de conformité',
            'Résultats des tests',
            'Contenus non accessibles',
            'Établissement de cette déclaration d\'accessibilité',
            'Retour d\'information et contact',
            'Voies de recours',
        );

        foreach ($sections as $section) {
            $this->assertStringContainsString(
                $section,
                $page,
                sprintf('accessibility page contains section "%s"', $section)
            );
        }

        // Compliance status statement and audit result
        $this->assertStringContainsString(
            'partiellement conforme',
            $page,
            'accessibility page states the compliance status'
        );

        $this->assertStringContainsString(
            '57 %',
            $page,
            'accessibility page states the audit result'
        );

        // Heading hierarchy: h1 provided by the base template, sections as h2
        $result = $crawler->filterXPath('//h1');
        $this->assertEquals(
            'Déclaration d\'accessibilité',
            $result->text(null, false),
            'h1 is Déclaration d\'accessibilité'
        );

        $this->assertGreaterThanOrEqual(
            7,
            $crawler->filterXPath('//h2')->count(),
            'accessibility page has one h2 per required section'
        );

        // Contact: the establishment's contact point, if configured.
        $this->config->setParam('a11yContact', '# Service informatique');

        $crawler = $client->request('GET', '/accessibility');
        $page = $crawler->filterXPath('//main')->text(null, false);

        $this->assertStringContainsString(
            'Service informatique',
            $page,
            'accessibility page displays the configured contact'
        );

        // Multi-year plan: hidden until configured
        $this->assertEquals(
            0,
            $crawler->filterXPath('//a[contains(text(), "Schéma pluriannuel")]')->count(),
            'multiannual plan link is hidden when not configured'
        );

        $this->config->setParam('a11yMultiannualPlan', 'https://example.org/schema');

        $crawler = $client->request('GET', '/accessibility');

        $this->assertEquals(
            1,
            $crawler->filterXPath('//a[@href="https://example.org/schema"]')->count(),
            'multiannual plan link is displayed when configured'
        );

        // Name of the organization subject to
        $this->config->setParam('a11yEntityName', 'My Compagny');

        $crawler = $client->request('GET', '/accessibility');
        $page = $crawler->filterXPath('//main')->text(null, false);

        $this->assertStringContainsString(
            'My Compagny',
            $page,
            'accessibility page displays the configured entity name'
        );

        // Legal remedies: mandatory information
        $this->assertStringContainsString(
            'Défenseur des droits',
            $page,
            'accessibility page mentions the Defender of Rights'
        );
 
        $this->assertStringContainsString(
            'Libre réponse 71120',
            $page,
            'accessibility page gives the postal address of the Defender of Rights'
        );
 
        $this->assertStringContainsString(
            '09 69 39 00 00',
            $page,
            'accessibility page gives the phone number of the Defender of Rights'
        );

        // Permanent link in the footer, stating the compliance status
        $result = $crawler->filterXPath('//footer');
        $this->assertStringContainsString(
            'Accessibilité : partiellement conforme',
            $result->text(null, false),
            'footer contains the accessibility link with the compliance status'
        );
    }

    public function testAccessibilityIsPubliclyAvailable(): void
    {
        $client = static::createClient();

        $client->request('GET', '/accessibility');

        $this->assertEquals(
            200,
            $client->getResponse()->getStatusCode(),
            'accessibility page is reachable without authentication'
        );

                $client = static::createClient();

        $crawler = $client->request('GET', '/accessibility');

        $this->assertEquals(
            200,
            $client->getResponse()->getStatusCode(),
            'accessibility page returns 200'
        );

        $page = $crawler->filterXPath('//main')->text(null, false);

        // The seven sections mandated by the Order of 20 September 2019
        $sections = array(
            'Engagement',
            'État de conformité',
            'Résultats des tests',
            'Contenus non accessibles',
            'Établissement de cette déclaration d\'accessibilité',
            'Retour d\'information et contact',
            'Voies de recours',
        );

        foreach ($sections as $section) {
            $this->assertStringContainsString(
                $section,
                $page,
                sprintf('accessibility page contains section "%s"', $section)
            );
        }
    }
}