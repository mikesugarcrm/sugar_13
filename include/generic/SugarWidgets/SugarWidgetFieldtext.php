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

class SugarWidgetFieldText extends SugarWidgetFieldVarchar
{
    public function queryFilterEquals($layout_def)
    {
        return $this->reporter->db->convert($this->_get_column_select($layout_def), 'text2char') .
            ' = ' . $this->reporter->db->quoted($layout_def['input_name0']);
    }

    public function queryFilterNot_Equals_Str($layout_def)
    {
        $column = $this->_get_column_select($layout_def);
        return "($column IS NULL OR " . $this->reporter->db->convert($column, 'text2char') . ' != ' .
            $this->reporter->db->quoted($layout_def['input_name0']) . ')';
    }

    public function queryFilterNot_Empty($layout_def)
    {
        $column = $this->_get_column_select($layout_def);
        return "($column IS NOT NULL AND " . $this->reporter->db->convert($column, 'length') . ' > 0)';
    }

    public function queryFilterEmpty($layout_def)
    {
        $column = $this->_get_column_select($layout_def);
        return "($column IS NULL OR " . $this->reporter->db->convert($column, 'length') . ' = 0)';
    }

    public function displayList($layout_def)
    {
        return nl2br(parent::displayListPlain($layout_def));
    }
}
