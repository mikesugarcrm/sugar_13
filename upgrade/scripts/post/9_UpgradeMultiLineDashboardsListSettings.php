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

/**
 * Convert muilt-line dashboards to focus dashboards.
 */
class SugarUpgradeUpgradeMultiLineDashboardsListSettings extends UpgradeScript
{
    public $order = 9901;
    public $type = self::UPGRADE_DB;

    /**
     * Execute upgrade tasks
     * @see UpgradeScript::run()
     */
    public function run()
    {
        if (version_compare($this->from_version, '13.3.0', '>=')) {
            return;
        }
        $this->log('Upgrading muilti-line dashboards list settings');

        $metricsBean = BeanFactory::newBean('Metrics');
        $query = new SugarQuery();
        $query->select(['id', 'viewdefs', 'metric_context']);
        $query->from($metricsBean, ['add_deleted' => false]);
        $query->where()
            ->in('metric_context', ['service_console', 'renewals_console']);
        $results = $query->execute();

        foreach ($results as $row) {
            $viewdefs = json_decode($row['viewdefs'], true);
            $panel = &$viewdefs['base']['view']['multi-line-list']['panels'][0];

            $shouldUpdate = false;

            foreach ($panel['fields'] as &$field) {
                if (isset($field['subfields'])) {
                    foreach ($field['subfields'] as &$subfield) {
                        $isUpdated = ($row['metric_context'] === 'service_console') ?
                            $this->updateServiceFields($subfield) :
                            $this->updateSalesFields($subfield);
                        $shouldUpdate = $shouldUpdate || $isUpdated;
                    }
                }
            }

            if ($shouldUpdate) {
                $query = 'UPDATE metrics SET viewdefs = ? WHERE id = ?';
                $this->db->getConnection()->executeUpdate(
                    $query,
                    [json_encode($viewdefs), $row['id']],
                    [\Doctrine\DBAL\ParameterType::STRING, \Doctrine\DBAL\ParameterType::STRING]
                );
            }
        }
    }

    /**
     * Update Service Console fields metadata
     *
     * @param $subfield
     * @return bool
     */
    private function updateServiceFields(&$subfield)
    {
        $fieldsToUnsetReadonly = [
            'name',
            'follow_up_datetime',
            'business_center_name',
        ];

        if (in_array($subfield['name'], $fieldsToUnsetReadonly) && isset($subfield['readonly'])) {
            unset($subfield['readonly']);
            return true;
        }

        return false;
    }

    /**
     * Update Sales Console fields metadata
     *
     * @param $subfield
     * @return bool
     */
    private function updateSalesFields(&$subfield)
    {
        $fieldsToSetReadonly = [
            'sales_stage',
            'date_closed',
        ];

        if (in_array($subfield['name'], $fieldsToSetReadonly) && !isset($subfield['readonly'])) {
            $subfield['readonly'] = true;
            return true;
        }

        return false;
    }
}
