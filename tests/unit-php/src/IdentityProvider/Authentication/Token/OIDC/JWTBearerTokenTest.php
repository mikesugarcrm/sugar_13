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

namespace Sugarcrm\SugarcrmTestsUnit\IdentityProvider\Authentication\Token\OIDC;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Token\OIDC\JWTBearerToken;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Token\OIDC\JWTBearerToken
 */
class JWTBearerTokenTest extends TestCase
{
    protected $privateKey = [
        'kty' => 'RSA',
        'kid' => 'private',
        'alg' => 'RS256',
        'n' => 'ziFqqp2RBokiirNkOs1wbJhp4huH_JHABuBBRYFXhfFJY-bKFWHi1SVsDr2rBb_690_H6lEHr04e3lE5L2Ze99hA1eQwjeKHe_' .
            'DtAwKjk7vnG0q08yAupgdsPIrcFtz42kTdxNDCl5sHvNsZIjiY3CUAuutOiVf9ZTmU6-1SYydZa5ApbzmCz7mXgOeuWc6smXX_us5' .
            'uekVHVFiy8c8GDY_GGj_Ber1ejvTOoUiiOL9KY-Wqixpnc-d0fXN-L-4I6MoMVhRV7ynCoJ1FRUTPaSVEKkVJgpRAxZezvJ0641PN' .
            'seL4hhJi1vZlsjeSgm2VQm59nvgLqjVTdN246GHbHWDqk2OKexICYMGsag1PVDPFTvzT9mc5x_ynkbevMBD9GFGgnKYMkEmVFAM9G' .
            'HaE8Ni_WNK1NC2qSLG-AnnIHVPbnfim9FZCgdqORuY406LlkjDS1GGmRmDetJEqQbTaEJ6CywSMo7oQh-monx3ZZHxDTtiyG4B_Xh' .
            'A6jfIb83nljQSZfVSuikwvwLm1TA59OIIJ40PE-olN2gqOayLwhuMhPsi4Tg7huJmDmqPfp9uXMSJh4s2I7XiK5LS8q0ccif1iaFL' .
            '2RxzMLQxT2uv1vRJZoCKoNzR3784rrR75aVXgf-GpfJ1i1utV4nzm7RIyeDaDdb0AJCV648OLAoaKEzU',
        'e' => 'AQAB',
        'd' => 'Dvvm4RgrHqqBVEvOEWg1r-80YzdVH0sJBnbux7qrPhVYHGb-cad38b6SqE-pSvW1rJykD6hsQpYPMGH_Ii7y4Flb_TBlRyscZi' .
            'oRUJK0iVyzZAx-Mt44BeGsQIpnjVHq1RMEe_Yg7xxZ56SVoyMyGW6nKu9H-jvnM6CH7s6FmqeVnHgSSv-HPspi9P_icKzRZyZovI-' .
            'dAE5g7QS1nVZLPlkhMW9JBT8WzJWHH7pD8JQXOEPNrebxdj9w_F2U4q8O_r0RQICh7oy-lSZZjrt9yErpNZlryo40Vyi77A4R5cyF' .
            'u1SgdD6J6M5ofhgEEm8c1oNpplCpqGnP80La2imi39JvWHDgkmmoceQY4DjbQBpUrvGqpnBS_zEskD_F_A7CNQ5ido-cnw_zG9mrJ' .
            'RcRE4lQUcKq-0HLPtORLRliyoxaXw_ToT-fGW9V_uT2TZOPWqpmOJZchZvuwjRCDjsh9bvtA8piJpQwJmj6BG-kx2laAGe7OTrdTx' .
            'IBFAbyMCvEpl1oEL2y9f-8ww6kCw9oimY6IMrRcx6Wr_4BkQYdYvbeG_je-lnYRwyLOZJ9kORJarLk200t-psLIxUSX2YLaZy-QB1' .
            'YXTrxPfd7bgcwdKOtiDXLMX6saqYeVJgRu3lBSfv0DADzIbAvpUHVt3ZNGACwldx9WuZ3_wAqOtBVj9E',
        'p' => '64AdyKYuATRiDS0kcOwOHwTmXAAiPyUCFTuy20ITT8cAN-YTtWH6CyHz1Iu98cI24C9Aq-w8YXoeDG1HP0EHo8xp_1yjr-H8S7' .
            'iNm8Be4YmhKSS-rvqdkWasuOfT0vinm5w0q1anvFRO0IMjWluteM5NWJyWolsYa6F0rfFawr3py3uKVDQMkQaJ5wQo1T8gd7rnYi4' .
            'LYVAym7kAG7wlsz-7I9XI_ERhtpv2HSTEXvqDH9aWnNQ_Lziix6DEVvenxx9Si4DikL3xO7q0LYERO9h_dDcNB7ekHIxI7_hISbwt' .
            '_JZcp_TFLOIJt7JCqjy-kgX-YjL6IB7Ha66nOw57nw',
        'q' => '4BLScxKNbe_7BZkMV6IzgsLrZ1ny8umYU41TV-ZZwmEX2o9-ueYysYs12aDel1gwRwaUOrwuyC9GAfTp8vTs5OL0qWahS2-536' .
            '9X-gQ_x1Pec1HteMpl8B7trJySkvw7V3EHAyDeM_hEpQ2McKv6m1j38xmNI27BDvzwbOMRMdg1RocoNqkmiIAd6-OO8NPQUJE9Y4L' .
            'XFIx0PRj2i3tA7nrVKccMcbG2ECLambEIyadEPXaLfV9b6EmLpCoQL2bj7FQFCP2-pYBSIVe6DkgvzqZRuaa5cW5UldePsEbLxL0v' .
            '3d9TRvDUTmTwHq2cN_9GHvo2YibURGXrU7XvrzKAqw',
    ];

    /**
     * @covers ::getCredentials
     */
    public function testGetCredentials()
    {
        $token = new JWTBearerToken('userId', 'srn:tenant');
        $this->assertNull($token->getCredentials());
    }

    /**
     * @covers ::getIdentity
     */
    public function testGetIdentity()
    {
        $token = new JWTBearerToken('userId', 'srn:tenant');
        $this->assertEquals('userId', $token->getIdentity());
    }

    /**
     * @covers ::__toString
     */
    public function testToString()
    {
        $expectedResult = 'eyJraWQiOiJLZXlJZCIsImFsZyI6IlJTMjU2In0.eyJpYXQiOjEwLCJleHAiOjMxMCwiYXVkIjoiaHR0cDovL2F1ci' .
            '51cmwiLCJzdWIiOiJzcm46Y2x1c3RlcjppZG06ZXU6MDAwMDAwMDAwMTp1c2VyOnNlZWRfc2FsbHlfaWQiLCJpc3MiOiJjbGllbnQiLC' .
            'J0aWQiOiJzcm46Y2x1c3RlcjppZG06ZXU6MDAwMDAwMDAwMTp0ZW5hbnQiLCJzdWRvZXIiOiJzcm46Y2x1c3RlcjppZG06ZXU6MDAwMD' .
            'AwMDAwMTp1c2VyOjI4Mzg2ZTI1LWEyMDktNGY2Ny04NjRkLWU1NjVlNzNkYWU2ZCJ9.zW8RRu9-SZIpUxV8KIoR8keIQBFBgMIO-9aJw' .
            'sBQiymf5NULOHHaKthAhmAxu-cS3X3tTs6HCjl_2KOM4-2yxmCtevDRniXZOEbzv6_KeaM7-FAOq_7ZjVhPdsnNhRBqQqvZhk2kqn63C' .
            'tr6hQSOD2YWNDSpzbSfZCZLV47C7jzkn6WKFO0plvfxET4VDCYfRfZk1f2KqCPHRH3m4fvJEPiqG9bTyh7N8ErDYj5v9Zf3qKtAyNgBP' .
            'XvMH_iGWxJoNOJIOiIr7m9s4gt7bZ1G5LKQxOuZbd8hlVC3lLMDgX9s1zlR-vsAKEEnOBC6snuLyZ2KB6pSLDMRj9U1n5-gSMoIpemfM' .
            'oyhaed4SJu1BOEwqRXlksXUv9nuBptiqTt-anV7EZlRTIRge15JCRQDs4SsXDkGg9OfCKbjqRCJv6RJeomA-R12Q1p6oqOr85IIIVLEl' .
            'XyvN9_XTbORSB2gkywKx3sldGvr7jIa5Ey5WZBo65JRfdB2ppUtP216BlL6qODj6XusxkO7IiJ8V31HhiQUeT9GQqSG31J8lU0F4ee3Y' .
            '3aWRr--XnDm_lHPFDp3wdrPWDwC99zyghBTBRCZ7i_rDSG0jebtaeWlTy4jLOQf2jrYEDYIp2KI6Izbza02BpM1gJeWd1hFyoRhADxKp' .
            'FhtlHOgOP9ICfRGYmQWz5U';

        $token = $this->getMockBuilder(JWTBearerToken::class)
            ->setConstructorArgs(
                ['srn:cluster:idm:eu:0000000001:user:seed_sally_id', 'srn:cluster:idm:eu:0000000001:tenant']
            )
            ->setMethods(['getUser'])
            ->getMock();

        $token->setAttribute('privateKey', $this->privateKey);
        $token->setAttribute('iat', 10);
        $token->setAttribute('aud', 'http://aur.url');
        $token->setAttribute('iss', 'client');
        $token->setAttribute('kid', 'KeyId');
        $token->setAttribute('sudoer', 'srn:cluster:idm:eu:0000000001:user:28386e25-a209-4f67-864d-e565e73dae6d');

        $this->assertEquals($expectedResult, (string)$token);
        $this->assertEquals('srn:cluster:idm:eu:0000000001:user:seed_sally_id', $token->getIdentity());
    }
}
