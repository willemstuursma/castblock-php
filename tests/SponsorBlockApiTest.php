<?php
namespace WillemStuursma\CastBlock\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use WillemStuursma\CastBlock\SponsorBlockApi;
use WillemStuursma\CastBlock\SponsorblockCategory;
use WillemStuursma\CastBlock\ValueObjects\Segment;

final class SponsorBlockApiTest extends \PHPUnit\Framework\TestCase
{
    private const SPONSORBLOCK_RESPONSE = /** @lang JSON */ '[
    {
        "videoID": "amALBl3kOtU",
        "hash": "eac5c8338a177391210d5770e938303c693a1cf08a4f186348c3c0edbbbcb974",
        "segments": [
            {
                "category": "interaction",
                "actionType": "skip",
                "segment": [
                    116,
                    136.221
                ],
                "UUID": "3e3f1715ba4add75c04754b08c16606566399cf22fb00f1aee91c91d8649a68a",
                "locked": 0,
                "votes": 0,
                "videoDuration": 0,
                "userID": "f7a2f8c01eab650d8dec699f480ee14b6bf82499b46c385fe5c9785ab08ae346",
                "description": ""
            }
        ]
    },
    {
        "videoID": "vZbYIY1-DJY",
        "hash": "eac55f042ae8ea8dc8cd86391206cc9bc17e6f5063d0538f009815904ac21067",
        "segments": [
            {
                "category": "sponsor",
                "actionType": "skip",
                "segment": [
                    0.197,
                    7.135
                ],
                "UUID": "349be3bd3db27cb68c4021a82110b7db68fdf907ae5579873e0f18d780addee6b",
                "locked": 0,
                "votes": 0,
                "videoDuration": 498,
                "userID": "56d3f700a872914bfe2e4c3053dbc808a811cac266e047d80ca06eb798850280",
                "description": ""
            }
        ]
    },
    {
        "videoID": "ecHDfz6BPwA",
        "hash": "eac5af20a47f85bb40fbe5fcebd81281fcc8fac951566dc2f95eb0a6f9dc4f29",
        "segments": [
            {
                "category": "interaction",
                "actionType": "skip",
                "segment": [
                    9.762281,
                    25.074812
                ],
                "UUID": "434be20c023d2fb959b6141e33442dcbe6ec4ae668c63ab24d8966a949eb1ecb",
                "locked": 0,
                "votes": 0,
                "videoDuration": 0,
                "userID": "5b089903c8b1c37b6e6ca4f0439dc1cdec825ab5eec683ec16d71eb0fcc9f09f",
                "description": ""
            }
        ]
    },
    {
        "videoID": "mOaW69MmHe0",
        "hash": "eac561201fabb9286b641821d0b3731ee442ec33cb5b4ff414bb54d0a7117d01",
        "segments": [
            {
                "category": "interaction",
                "actionType": "skip",
                "segment": [
                    43.244,
                    47.551
                ],
                "UUID": "40b5927a117ffee1faac2a0bbb808d4eb1fe272c932835202adb99ba105524bde",
                "locked": 0,
                "votes": 0,
                "videoDuration": 1472.101,
                "userID": "c4da5e5a975f8869248d8fdb35a494778334e544bdfd9dbc78c577e9fe5ab804",
                "description": ""
            }
        ]
    },
    {
        "videoID": "IdgCkUJNTYs",
        "hash": "eac59d498c1e05fdbf232a91eb503d2b3e02389d95ec9dca0462e28d0beb2469",
        "segments": [
            {
                "category": "sponsor",
                "actionType": "skip",
                "segment": [
                    33.741,
                    81.883
                ],
                "UUID": "523aad0f79c206fbf4162f5b54100b8a07ea6fd0e73ac45bdf88e91da02a8ff9a",
                "locked": 0,
                "votes": 2,
                "videoDuration": 1754.601,
                "userID": "fda2e0799f5e5b476d6b90e75eb70de69fd5e02ab877ecb29cda00686547e290",
                "description": ""
            },
            {
                "category": "interaction",
                "actionType": "skip",
                "segment": [
                    186.517,
                    194.594
                ],
                "UUID": "5ac15b96abe443cc0fa538ddc22c451896483439c5b6f2f05923fb3fb063c2e20",
                "locked": 0,
                "votes": 2,
                "videoDuration": 1754.601,
                "userID": "fda2e0799f5e5b476d6b90e75eb70de69fd5e02ab877ecb29cda00686547e290",
                "description": ""
            },
            {
                "category": "sponsor",
                "actionType": "skip",
                "segment": [
                    187.428,
                    192.944
                ],
                "UUID": "56cb21a6517a5eba7922d42ed3465d9b0ffab5c2c0c2bc2ee9977e76438bd2225",
                "locked": 0,
                "votes": 0,
                "videoDuration": 1755,
                "userID": "7e04451438b5c85d1134b5ae10ac6faeaa3fa8eb5c7607d528043d60ad01efde",
                "description": ""
            }
        ]
    },
    {
        "videoID": "oLXXpWL_uy0",
        "hash": "eac557ad9ad5318def75944bdc914060c2674ea54b04f2e182f59ad974952d46",
        "segments": [
            {
                "category": "sponsor",
                "actionType": "skip",
                "segment": [
                    53.46,
                    83.35
                ],
                "UUID": "597ec974d42da2e869993ea5c50a66a51a554f59c17f82e3234376f88f8ebaca7",
                "locked": 0,
                "votes": 0,
                "videoDuration": 547.861,
                "userID": "72f8424afc93c9ba6bd93c3e20a22fb2d3e036637c8154f1eee82f158c5eb637",
                "description": ""
            }
        ]
    },
    {
        "videoID": "f4d4IAYLk1k",
        "hash": "eac5a01fe913835f41b2e970a17ab5d5c75f2eb8bdd2e48b97952b77ee1a8941",
        "segments": [
            {
                "category": "sponsor",
                "actionType": "skip",
                "segment": [
                    887.1908,
                    937.73505
                ],
                "UUID": "7abb7935d8392ceaffa0486a6112464fd9d13779b86ce36a1710e59fae3e6e30",
                "locked": 0,
                "votes": 0,
                "videoDuration": 967,
                "userID": "3805c744974bc96864e24b3a5b1b46c2a97c828f4f1717f888288383b197d260",
                "description": ""
            }
        ]
    },
    {
        "videoID": "_wxpR_OzaBs",
        "hash": "eac5aaf0e079db780ba56e7eb0b3a8819ddaca649a7f189c4d5728996cce8da0",
        "segments": [
            {
                "category": "sponsor",
                "actionType": "skip",
                "segment": [
                    81.871,
                    107.244
                ],
                "UUID": "586dc6e7642de340547a403085e338c7ea45f35ddbf4401e1ee40e1465941f30",
                "locked": 0,
                "votes": 0,
                "videoDuration": 0,
                "userID": "6c6fdec66cc3029345d809c6545c4adb217c9dd5cec8db838734701bd05f5412",
                "description": ""
            }
        ]
    },
    {
        "videoID": "X_a01xrwEcc",
        "hash": "eac5d517aacbfb7736b7aa39f982196f752e48a9fc99d8f64c8f6df38aa22f5a",
        "segments": [
            {
                "category": "sponsor",
                "actionType": "skip",
                "segment": [
                    119.78,
                    154.577
                ],
                "UUID": "5ede5bdaab80bc0d80a4bfb761ae44cbeadb8f2fd6d0af67ffb8b24a2627e1f48",
                "locked": 0,
                "votes": 0,
                "videoDuration": 1531.021,
                "userID": "03b814e23e021f7f023a34b6d892e3729b17af8ab1fb1d15266cb18fcf371329",
                "description": ""
            }
        ]
    },
    {
        "videoID": "F-VK9wbvqDo",
        "hash": "eac50cfa9301d6117db436ee5938361ea8dcbe870832842c1a45248532b8ccad",
        "segments": [
            {
                "category": "sponsor",
                "actionType": "skip",
                "segment": [
                    150.428,
                    225.413
                ],
                "UUID": "5ba9700a933c2ce2636e86f7fc88af1138a92e5a9155b645f69a2e66ed4eeb7ed",
                "locked": 0,
                "votes": 1,
                "videoDuration": 1919,
                "userID": "1589152c74a1f6ba06970baf98ccc8a63fb8d9e9c8c3206413f9555fb71799fd",
                "description": ""
            }
        ]
    },
    {
        "videoID": "RQ7I-FJKdbs",
        "hash": "eac566bbfebf501109f9cf583dc22353b73eeba069047f3a0da4729adb37cc7f",
        "segments": [
            {
                "category": "interaction",
                "actionType": "skip",
                "segment": [
                    389.714,
                    412.461
                ],
                "UUID": "9fba55f1f4868781ecc5b60cfdd1e3c19c8e044fcbab016e1ba0fd6d929446b7",
                "locked": 0,
                "votes": 0,
                "videoDuration": 0,
                "userID": "662ed9e7c6c278b49624e275d19a5ab88293129d2fcc4b0041be07a225d07468",
                "description": ""
            },
            {
                "category": "sponsor",
                "actionType": "skip",
                "segment": [
                    1162.008,
                    1229.592
                ],
                "UUID": "13c0201da0ad78401704cd89e1ec0ad638e02f28b8f7257b9c0c6653086edcab",
                "locked": 0,
                "votes": 0,
                "videoDuration": 0,
                "userID": "662ed9e7c6c278b49624e275d19a5ab88293129d2fcc4b0041be07a225d07468",
                "description": ""
            }
        ]
    },
    {
        "videoID": "mEySJkXezeg",
        "hash": "eac5682b5635113957fb98d1dc3ed99956aeec2f959c3e351619814688fb3988",
        "segments": [
            {
                "category": "sponsor",
                "actionType": "skip",
                "segment": [
                    837.748,
                    941.296
                ],
                "UUID": "5ef1bb0e0af7847f3de4f94ab9c343d13b6a7c97c47b7b47444886f0e3dec296e",
                "locked": 0,
                "votes": 0,
                "videoDuration": 1710.521,
                "userID": "ebda2a9182fae9740ec9943e4f001af868221e3de26a536c73c6cc7dc09bd2b2",
                "description": ""
            }
        ]
    }
]';

    /**
     * @var SponsorBlockApi
     */
    private $sponsorBlock;
    /**
     * @var Client|MockObject
     */
    private $httpClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->httpClient = $this->createMock(Client::class);

        $this->sponsorBlock = new SponsorBlockApi($this->httpClient);
    }

    public function testSegmentsAreFound()
    {
        $videoId = "IdgCkUJNTYs";

        $response = new Response(200, [], self::SPONSORBLOCK_RESPONSE);

        $this->httpClient->expects($this->once())
            ->method("get")
            ->with("https://sponsor.ajay.app/api/skipSegments/eac59d49?categories=%5B%22interaction%22%2C%22sponsor%22%5D")
            ->willReturn($response);

        $segments = $this->sponsorBlock->getSegments($videoId, [
            SponsorblockCategory::INTERACTION(),
            SponsorblockCategory::SPONSOR(),
        ]);

        $this->assertCount(3, $segments);

        $expected = [
            new Segment($videoId, 33.741, 81.883),
            new Segment($videoId, 186.517, 194.594),
            new Segment($videoId, 187.428, 192.944),
        ];

        $this->assertEquals($expected, $segments);
    }
}