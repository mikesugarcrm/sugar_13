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

/**
 * Bug 46984:
 *  EmailTemplate.parse_tracker_urls
 * @ticket 46984
 * @author arymarchik@sugarcrm.com
 */
class Bug46984Test extends TestCase
{
    protected function setUp(): void
    {
        $beanList = [];
        $beanFiles = [];
        require 'include/modules.php';
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);

        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser(true, 1);
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    /**
     * Testing EmailTemplate::parse_tracker_urls
     * @group 46984
     * @dataProvider templatesProvider
     */
    public function testParseTrackerUrl($data, $expects, $result)
    {
        $et = new EmailTemplate();
        $res = $et->parse_tracker_urls($data[0], $data[1], $data[2], $data[3]);
        if ($result) {
            $this->assertEquals($expects, $res);
        } else {
            $this->assertNotEquals($expects, $res);
        }
    }

    public function templatesProvider()
    {
        global $sugar_config;
        $result = [];
        $text = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin id leo at eros dictum fringilla. In sit amet est lacus. Vivamus luctus dapibus erat, vitae malesuada elit hendrerit ut. Etiam convallis auctor lectus eu blandit. Fusce condimentum sodales metus, et aliquam eros ornare eget. Quisque ultrices risus arcu. Aliquam sit amet turpis nunc.

 Nam congue, lorem vitae congue dapibus, nulla nulla tristique turpis, ut convallis enim libero in quam. Nulla feugiat, ligula nec sagittis lobortis, massa odio hendrerit nisl, at vehicula arcu sapien at magna. Duis quis justo nisl. Proin tempus nunc et nulla tincidunt vehicula. In vestibulum euismod sollicitudin. Cras dictum lectus pharetra nibh eleifend iaculis. Praesent vitae est enim, a mattis massa. Morbi accumsan ligula eu dui iaculis a laoreet lectus elementum. Maecenas a odio augue. Suspendisse congue, turpis id venenatis ornare, enim justo commodo magna, a ornare mauris lacus at eros. Suspendisse pretium cursus dui ut bibendum. Vivamus euismod, erat ac dignissim tincidunt, odio augue cursus elit, a venenatis ante nunc et urna. Integer dignissim gravida dui vitae facilisis. Sed nec turpis id elit venenatis mattis.

 Nam cursus consectetur neque, vel mattis augue gravida nec. Quisque est massa, fermentum ut fermentum et, sagittis ac lacus. Quisque vulputate sagittis ultricies. Fusce mattis lectus eget urna elementum vitae condimentum libero ultrices. Aenean accumsan, diam quis fermentum placerat, enim quam eleifend justo, ac dictum massa magna a nunc. Sed id quam lectus, id sodales lectus. Maecenas ut lorem quis urna pulvinar blandit et in erat. Phasellus tortor nibh, luctus id suscipit non, laoreet in nisi. Donec aliquet accumsan lorem, vitae cursus enim feugiat at. Ut in felis lectus, at sollicitudin nibh. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Quisque sit amet sagittis urna. Cras aliquet orci at lectus dignissim vel convallis nunc pretium. Proin eget tortor risus, a accumsan velit. Aliquam a dui libero, non dignissim leo. Proin sed tellus tellus.

 Integer luctus ligula id justo dapibus facilisis. Aenean non ipsum at justo rhoncus lacinia. Sed in risus sed erat malesuada malesuada in in tellus. Donec sit amet erat libero, eget ultricies justo. Curabitur viverra urna quis tellus scelerisque et porttitor justo malesuada. Donec ultrices, lorem sed posuere accumsan, mi nulla scelerisque mi, eget lacinia urna metus quis sem. Donec ullamcorper ante scelerisque odio interdum consectetur. Ut a mi ligula, ac interdum tortor. Sed lectus quam, fringilla ut condimentum id, vehicula sit amet tortor. Curabitur lacinia fringilla consequat. Curabitur sit amet erat sed enim sagittis viverra lacinia ut nisi. Sed ac ligula dolor. Suspendisse lacinia, sem ut rhoncus congue, ligula ligula interdum urna, id sollicitudin massa ligula vel erat. Mauris lobortis imperdiet orci, ac fermentum velit dignissim eu.

 Phasellus metus felis, lacinia eget malesuada sed, porta at arcu. Aliquam erat volutpat. Aenean pretium sapien at nisl faucibus pellentesque. Mauris tristique lorem non arcu elementum accumsan tincidunt lacus scelerisque. Nulla convallis elementum libero et eleifend. Quisque lectus urna, imperdiet vitae laoreet rutrum, condimentum a tortor. Quisque in enim sed lacus elementum aliquet. Aliquam ultrices hendrerit odio, quis luctus neque ultrices vel. Sed felis quam, auctor nec convallis at, porta ut purus. Aliquam vitae tempor enim. Nunc rutrum augue quis justo tincidunt eu rutrum nisl lobortis. Sed id lorem ante. Donec enim erat, fringilla sed cursus eget, posuere sit amet nunc. Nulla tempus consectetur viverra. Proin odio odio, malesuada sit amet placerat nec, vulputate nec diam. Cras facilisis molestie auctor.';
        $text_len = strlen($text);
        $removeme = 'bad_trackers_url';
        for ($i = 0; $i < 10; $i++) {
            $res1 = $res2 = $text;
            $tracker_urls = [];
            $guid = create_guid();
            $j_rand = random_int(3, 7);
            $limit = floor($text_len / $j_rand);
            for ($j = $j_rand; $j > 1; $j--) {
                $guid_tr = create_guid();
                $tr_name = "tr_url{$j}";
                $tracker_urls["{{$tr_name}}"] = [
                    'tracker_name' => $tr_name,
                    'tracker_key' => $j,
                    'id' => $guid_tr,
                    'is_optout' => $j % 2,
                ];

                $ins_ind = random_int($limit * ($j - 2), $limit * ($j - 1));
                $res1 = substr_replace($res1, "{{$tr_name}}", $ins_ind, 0);
                if ($j % 2 == 1) {
                    $res2 = substr_replace($res2, $removeme, $ins_ind, 0);
                } else {
                    $res2 = substr_replace($res2, "{$sugar_config['site_url']}index.php?entryPoint=campaign_trackerv2&track={$tracker_urls["{{$tr_name}}"]['id']}&{$guid}", $ins_ind, 0);
                }
            }
            $result[$i] = [
                [
                    ['body' => $res1],
                    "{$sugar_config['site_url']}index.php?entryPoint=campaign_trackerv2&track=%s&{$guid}",
                    $tracker_urls,
                    $removeme,
                ],
                ['body' => $res2],
                true,
            ];
        }
        return $result;
    }
}
