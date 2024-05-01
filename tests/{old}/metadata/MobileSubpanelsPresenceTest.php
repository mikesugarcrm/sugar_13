<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

use PHPUnit\Framework\TestCase;

class MobileSubpanelsPresenceTest extends TestCase
{
    /**
     * Use this to define subpanels, which were intentionally removed from mobile OOTB
     *
     * @var array
     */
    private $ignoreSubpanels = [
        /*
        'Contacts' => [                     // module name
            'message_invites' => true,      // link names
            'archived_emails' => true,
        ],
        */
    ];

    private $missingSubpanels = [];

    private $varDefs = [];

    private $relationships = [];
    private $mobileMdm;
    private $baseMdm;
    private $listMobile;
    private $listBase;

    public function setUp(): void
    {
        SugarTestHelper::setUp('current_user', [true, true]);

        $this->mobileMdm = MetaDataManager::getManager('mobile');
        $this->baseMdm = MetaDataManager::getManager('base');
        $this->relationships = $this->mobileMdm->getRelationshipData();
        $this->listMobile = $this->mobileMdm->getModuleList(false);
        unset($this->listMobile['_hash']);
        $this->listBase = $this->baseMdm->getModuleList(false);
    }

    public function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }

    public function testMobileSubpanelsMatchDesktop()
    {
        foreach ($this->listMobile as $moduleName) {
            $layoutsBase = $this->baseMdm->getModuleLayouts($moduleName);
            $layoutsMobile = $this->mobileMdm->getModuleLayouts($moduleName);

            $this->varDefs[$moduleName] = $this->mobileMdm->getVarDef($moduleName);

            $map = [];
            $mobileSubpanels = $layoutsMobile['subpanels']['meta']['components'] ?? null;
            if (!$mobileSubpanels) {
                continue;
            }
            foreach ($mobileSubpanels as $subpanel) {
                if (!isset($subpanel['layout']) || $subpanel['layout'] !== 'subpanel') {
                    continue;
                }
                $link = $subpanel['context']['link'] ?? null;
                if ($link && isset($map[$link])) {
                    $this->fail("Duplicate subpanel for the link: {$moduleName}->{$link}");
                }
                $map[$link] = true;
            }

            $baseSubpanels = $layoutsBase['subpanels']['meta']['components'] ?? [];
            foreach ($baseSubpanels as $subpanel) {
                $link = $subpanel['context']['link'] ?? null;
                if ($link && !isset($map[$link]) && !isset($this->ignoreSubpanels[$moduleName][$link])
                    && $this->isValidLink($moduleName, $link)
                ) {
                    $this->markMissing($moduleName, $link);
                }
            }
        }

        $this->assertEmpty($this->missingSubpanels, $this->formatMissing());
    }

    private function markMissing(string $module, string $link): void
    {
        if (!isset($this->missingSubpanels[$module])) {
            $this->missingSubpanels[$module] = [];
        }
        $this->missingSubpanels[$module][] = $link;
    }

    private function formatMissing(): string
    {
        $lines = [];
        foreach ($this->missingSubpanels as $module => $links) {
            $lines[] = $module . '(' . implode(',', $links) . ')';
        }
        return 'Some subpanels exist on desktop, but missing on mobile: ' . implode(', ', $lines);
    }

    private function isValidLink($module, $link)
    {
        $field = $this->varDefs[$module]['fields'][$link] ?? null;
        if (!$field || $field['type'] !== 'link') {
            return false;
        }

        $relName = $field['relationship'] ?? null;
        if (!$relName || !isset($this->relationships[$relName])) {
            return false;
        }

        $rel = $this->relationships[$relName];
        if ($module === $rel['rhs_module']) {
            $linkedModule = $rel['lhs_module'];
        } elseif ($module === $rel['lhs_module']) {
            $linkedModule = $rel['rhs_module'];
        } else {
            return false;
        }

        if (!isset($this->listMobile[$linkedModule])) {
            return false;
        }

        return true;
    }
}
