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
declare(strict_types=1);

use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;
use Regression\SugarCRMRegression;

class StoredXssInDocMergeRegression extends SugarCRMRegression
{
    public function getRegressionDescription(): string
    {
        return '[BR-10668]: Stored XSS on https://bugbounty-1.managed.ms.sugarcrm.com/#Documents via "Doc Merge" template';
    }

    public function run(): void
    {
        $fileGuid = 'XS<img src=x onerror=alert()>S_PDF.pdf';

        $scenario = $this
            ->login('admin', 'asdf')
            ->uploadDocumentTempFile($fileGuid)
            ->expectStatusCode(200)
            ->extract('fileId', function (ResponseInterface $response): ?string {
                $body = json_decode($response->getBody()->getContents(), true);

                return $body['record']['id'];
            });

        $fileId = $scenario->getVar('fileId');

        $scenario
            ->apiCall(
                '/Documents?erased_fields=true&viewed=1',
                'POST',
                [
                    'deleted' => false,
                    'doc_type' => 'Sugar',
                    'revision' => '1',
                    'is_template' => false,
                    'assigned_user_id' => '1',
                    'status_id' => 'Active',
                    'template_type' => '',
                    'active_date' => '2023-09-05',
                    'category_id' => '',
                    'subcategory_id' => '',
                    'team_name' => [
                        [
                            'id' => '1',
                        ],
                    ],
                    'filename' => $fileGuid,
                    'document_name' => $fileGuid,
                    'filename_guid' => $fileId,
                ],
            )
            ->expectStatusCode(200)
            ->extract('documentId', function (ResponseInterface $response) {
                $body = json_decode($response->getBody()->getContents(), true);

                return $body['id'];
            });

        $documentId = $scenario->getVar('documentId');

        $scenario
            ->apiCall(
                '/DocumentMerges?erased_fields=true&viewed=1',
                'POST',
                [
                    'deleted' => false,
                    'status' => 'success',
                    'merge_type' => 'merge',
                    'file_type' => 'doc',
                    'generated_document_id' => $documentId,
                    'dismissed' => false,
                    'assigned_user_id' => '1',
                    'name' => 'test',
                    'team_name' => [
                        [
                            'id' => '1',
                        ],
                    ],
                ],
            )
            ->extract('docmergeId', function (ResponseInterface $response) {
                $body = json_decode($response->getBody()->getContents(), true);

                return $body['id'];
            });

        $docmergeId = $scenario->getVar('docmergeId');
        $hyperlinkUrl = $this->prependBase("/Documents/$docmergeId/file/filename?format=sugar-html-json&platform=base");

        $scenario
            ->apiCall(
                '/Notifications?erased_fields=true',
                'POST',
                [
                    'assigned_user_id' => '1',
                    'created_by' => '1',
                    'deleted' => false,
                    'description' => "<a href='$hyperlinkUrl'>$fileGuid</a>",
                    'is_read' => false,
                    'name' => $fileGuid,
                    'parent_id' => '',
                    'parent_type' => '',
                    'severity' => 'Document Widget List',
                ],
            )
            ->expectStatusCode(200);

        $scenario
            ->apiCall('/metadata?type_filter=currencies%2Cfull_module_list%2Cmodules_info%2Chidden_subpanels%2Cjssource%2Cjssource_public%2Cordered_labels%2Cmodule_tab_map%2Cmodules%2Crelationships%2Cserver_info%2Cconfig%2C_override_values%2Cfilters%2Clogo_url%2Clogo_url_dark%2Ceditable_dropdown_filters&platform=base&module_dependencies=1')
            ->extract('jsSource', function (ResponseInterface $response) {
                $body = json_decode($response->getBody()->getContents(), true);

                return $body['jssource'];
            });

        $jsSource = $scenario->getVar('jsSource');

        $request = new Request('GET', $jsSource);

        $scenario
            ->send($request)
            ->expectStatusCode(200)
            ->expectRegexp("/if\s*\(this\.name\s*===\s*'description'\)\s*\{\s*value\s*=\s*DOMPurify.sanitize\(value\)/");
    }

    public function uploadDocumentTempFile(
        string $filename,
        string $contents = '',
        array  $headers = [],
        array  $options = []
    ): self {
        $request = new Request(
            'POST',
            $this->prependBase('/Documents/temp/file/filename?platform=base'),
            $headers,
        );

        $options = [
                'multipart' => [
                    [
                        'Content-Type' => 'multipart/form-data',
                        'name' => 'filename',
                        'contents' => $contents,
                        'filename' => $filename,
                    ],
                ],
            ] + $options;

        return $this->send($request, $options);
    }
}
