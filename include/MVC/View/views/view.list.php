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

class ViewList extends SugarView
{
    /**
     * @var \SugarBean|null|mixed
     */
    public $saved_search;
    public $view;
    public $type = 'list';
    public $lv;
    public $searchForm;
    public $use_old_search;
    public $headers;
    public $seed;
    public $params;
    public $listViewDefs;
    public $storeQuery;
    public $where = '';

    public function oldSearch()
    {
    }

    public function newSearch()
    {
    }

    public function listViewPrepare()
    {
        $listViewDefs = [];
        $module = $GLOBALS['module'];

        $metadataFile = $this->getMetaDataFile();

        if (empty($metadataFile)) {
            sugar_die($GLOBALS['app_strings']['LBL_NO_ACTION']);
        }

        require $metadataFile;

        $this->listViewDefs = $listViewDefs;
        $this->bean->ACLFilterFieldList($this->listViewDefs[$module], ['owner_override' => true]);

        if (!empty($this->bean->object_name) && isset($_REQUEST[$module . '2_' . strtoupper($this->bean->object_name) . '_offset'])) {//if you click the pagination button, it will populate the search criteria here
            if (!empty($_REQUEST['current_query_by_page'])) {//The code support multi browser tabs pagination
                $blockVariables = ['mass', 'uid', 'massupdate', 'delete', 'merge', 'selectCount', 'request_data', 'current_query_by_page', $module . '2_' . strtoupper($this->bean->object_name) . '_ORDER_BY'];
                if (isset($_REQUEST['lvso'])) {
                    $blockVariables[] = 'lvso';
                }

                $current_query_by_page = unserialize(base64_decode((string)$_REQUEST['current_query_by_page']), ['allowed_classes' => false]);

                foreach ($current_query_by_page as $search_key => $search_value) {
                    if ($search_key != $module . '2_' . strtoupper($this->bean->object_name) . '_offset' && !in_array($search_key, $blockVariables)) {
                        if (!is_array($search_value)) {
                            $_REQUEST[$search_key] = securexss($search_value);
                        } else {
                            foreach ($search_value as $key => &$val) {
                                $val = securexss($val);
                            }
                            $_REQUEST[$search_key] = $search_value;
                        }
                    }
                }
            }
        }
        if (!empty($_REQUEST['saved_search_select'])) {
            if ($_REQUEST['saved_search_select'] == '_none' || !empty($_REQUEST['button'])) {
                $_SESSION['LastSavedView'][$_REQUEST['module']] = '';
                unset($_REQUEST['saved_search_select']);
                unset($_REQUEST['saved_search_select_name']);

                //use the current search module, or the current module to clear out layout changes
                if (!empty($_REQUEST['search_module']) || !empty($_REQUEST['module'])) {
                    $mod = !empty($_REQUEST['search_module']) ? $_REQUEST['search_module'] : $_REQUEST['module'];
                    global $current_user;
                    //Reset the current display columns to default.
                    $current_user->setPreference('ListViewDisplayColumns', [], 0, $mod);
                }
            } elseif (empty($_REQUEST['button']) && (empty($_REQUEST['clear_query']) || $_REQUEST['clear_query'] != 'true')) {
                $this->saved_search = BeanFactory::newBean('SavedSearch');
                $this->saved_search->retrieveSavedSearch($_REQUEST['saved_search_select']);
                $this->saved_search->populateRequest();
            } elseif (!empty($_REQUEST['button'])) { // click the search button, after retrieving from saved_search
                $_SESSION['LastSavedView'][$_REQUEST['module']] = '';
                unset($_REQUEST['saved_search_select']);
                unset($_REQUEST['saved_search_select_name']);
            }
        }
        $this->storeQuery = new StoreQuery();
        if (!isset($_REQUEST['query'])) {
            $this->storeQuery->loadQuery($this->module);
            $this->storeQuery->populateRequest();
        } else {
            $this->storeQuery->saveFromRequest($this->module);
        }

        $this->seed = $this->bean;

        $displayColumns = [];
        if (!empty($_REQUEST['displayColumns'])) {
            foreach (explode('|', (string)$_REQUEST['displayColumns']) as $num => $col) {
                if (!empty($this->listViewDefs[$module][$col])) {
                    $displayColumns[$col] = $this->listViewDefs[$module][$col];
                }
            }
        } else {
            foreach ($this->listViewDefs[$module] as $col => $this->params) {
                if (!empty($this->params['default']) && $this->params['default']) {
                    $displayColumns[$col] = $this->params;
                }
            }
        }
        $this->params = ['massupdate' => true];
        if (!empty($_REQUEST['orderBy'])) {
            $this->params['orderBy'] = $this->request->getValidInputRequest('orderBy', 'Assert\Sql\OrderBy');
            $this->params['overrideOrder'] = true;
            if (!empty($_REQUEST['sortOrder'])) {
                $this->params['sortOrder'] = $_REQUEST['sortOrder'];
            }
        }
        $this->lv->displayColumns = $displayColumns;

        $this->module = $module;

        $this->prepareSearchForm();

        if (isset($this->options['show_title']) && $this->options['show_title']) {
            $moduleName = $this->seed->module_dir ?? $GLOBALS['mod_strings']['LBL_MODULE_NAME'];
            echo $this->getModuleTitle(true);
        }
    }

    public function listViewProcess()
    {
        $this->processSearchForm();
        $this->lv->searchColumns = $this->searchForm->searchColumns;

        if (!$this->headers) {
            return;
        }
        if (empty($_REQUEST['search_form_only']) || $_REQUEST['search_form_only'] == false) {
            $this->lv->ss->assign('SEARCH', true);
            $this->lv->setup($this->seed, 'include/ListView/ListViewGeneric.tpl', $this->where, $this->params);
            $savedSearchName = empty($_REQUEST['saved_search_select_name']) ? '' : (' - ' . $_REQUEST['saved_search_select_name']);
            echo $this->lv->display();
        }
    }

    public function prepareSearchForm()
    {
        $this->searchForm = null;

        //search
        $view = 'basic_search';
        if (!empty($_REQUEST['search_form_view']) && $_REQUEST['search_form_view'] == 'advanced_search') {
            $view = $_REQUEST['search_form_view'];
        }
        $this->headers = true;

        if (!empty($_REQUEST['search_form_only']) && $_REQUEST['search_form_only']) {
            $this->headers = false;
        } elseif (!isset($_REQUEST['search_form']) || $_REQUEST['search_form'] != 'false') {
            if (isset($_REQUEST['searchFormTab']) && $_REQUEST['searchFormTab'] == 'advanced_search') {
                $view = 'advanced_search';
            } else {
                $view = 'basic_search';
            }
        }
        $this->view = $view;

        $this->use_old_search = true;
        if (SugarAutoLoader::existingCustom('modules/' . $this->module . '/SearchForm.html') &&
            !SugarAutoLoader::existingCustom('modules/' . $this->module . '/metadata/searchdefs.php')) {
            require_once 'include/SearchForm/SearchForm.php';
            $this->searchForm = new SearchForm($this->module, $this->seed);
        } else {
            $this->use_old_search = false;
            require_once 'include/SearchForm/SearchForm2.php';

            $searchMetaData = SearchForm::retrieveSearchDefs($this->module);

            $this->searchForm = $this->getSearchForm2($this->seed, $this->module, $this->action);
            $this->searchForm->setup($searchMetaData['searchdefs'], $searchMetaData['searchFields'], 'SearchFormGeneric.tpl', $view, $this->listViewDefs);
            $this->searchForm->lv = $this->lv;
        }
    }

    public function processSearchForm()
    {
        if (isset($_REQUEST['query'])) {
            // we have a query
            if (!empty($_SERVER['HTTP_REFERER']) && preg_match('/action=EditView/', (string)$_SERVER['HTTP_REFERER'])) { // from EditView cancel
                $this->searchForm->populateFromArray($this->storeQuery->query);
            } else {
                $this->searchForm->populateFromRequest();
            }

            $where_clauses = $this->searchForm->generateSearchWhere(true, $this->seed->module_dir);

            if (safeCount($where_clauses) > 0) {
                $this->where = '(' . implode(' ) AND ( ', $where_clauses) . ')';
            }
            $GLOBALS['log']->info("List View Where Clause: $this->where");
        }
        if ($this->use_old_search) {
            switch ($this->view) {
                case 'basic_search':
                    $this->searchForm->setup();
                    $this->searchForm->displayBasic($this->headers);
                    break;
                case 'advanced_search':
                    $this->searchForm->setup();
                    $this->searchForm->displayAdvanced($this->headers);
                    break;
                case 'saved_views':
                    echo $this->searchForm->displaySavedViews($this->listViewDefs, $this->lv, $this->headers);
                    break;
            }
        } else {
            echo $this->searchForm->display($this->headers);
        }
    }

    public function preDisplay()
    {
        $this->lv = new ListViewSmarty();
    }

    public function display()
    {
        if (!$this->bean || !$this->bean->ACLAccess('list')) {
            ACLController::displayNoAccess();
        } else {
            $this->listViewPrepare();
            $this->listViewProcess();
        }
    }

    /**
     *
     * @return SearchForm
     */
    protected function getSearchForm2($seed, $module, $action = 'index')
    {
        // SearchForm2.php is required_onced above before calling this function
        // hence the order of parameters is different from SearchForm.php
        return new SearchForm($seed, $module, $action);
    }
}
