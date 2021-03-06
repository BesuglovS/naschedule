<?php

namespace App\Http\Controllers;

ini_set('max_execution_time', 600);

use App\DomainClasses\Calendar;
use App\DomainClasses\ConfigOption;
use App\DomainClasses\Discipline;
use App\DomainClasses\Faculty;
use App\DomainClasses\Lesson;
use App\DomainClasses\StudentGroup;
use App\DomainClasses\Teacher;
use App\User;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use DateTime;
use Dompdf\Exception;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TrelloController extends Controller
{
    public static $trelloListIds = array(
        1 => array(
            '5 Д' => '5f4fceccea96c8742e6a982a',
            '6 Г' => '5f4e6d41082a154cba2a6d68',
            '8 В' => '5f4e6e30320f9d41cc091fce',
        ),
        2 => array(
            '5 А' => '5f54bee8f648501a602b6756', '5 Б' => '5f54beebcbc8c324fd17709d', '5 В' => '5f54beed2067cc829b8dc882', '5 Г' => '5f54bef088cf8b2f033144cb', '5 Д' => '5f54bef2992f4452f0c555de',
            '6 Б' => '5f57b42b9f9bad4c5b063821', '6 Г' => '5f54bf8a8d152044977245b0',
            '8 В' => '5f54bfeac6bd18284d3c88f8",
',
        ),
        3 => array(
            '5 А' => '5f5c863ae04692770b346c44', '5 Б' => '5f5c863fadb0385bbd0d61af', '5 В' => '5f5c8642d357784f507e4010', '5 Г' => '5f5c864565dad27c60041b84', '5 Д' => '5f5c8648b2495c5fcbae15cf',
            '6 Б' => '5f5c87d587aca316ca356785', '6 Г' => '5f5c87d7b189e85749fdca94',
            '8 В' => '5f5c88a0a1c1af4f5f18ba16',
        ),
        4 => array(
            '5 Д' => '5f67331a863e431b829cdf01',
            '6 Б' => '5f6733a156209f5b4a0efe6f', '6 Г' => '5f6733a453a8ab8a0d3705f3',
            '8 В' => '5f67354d7f86144dfba0f486',
        ),
        5 => array(
            '6 Г' => '5f7322aeead27073e6e3dc47',
            '8 Б' => '5f72c9ef27711f6f0c2717bb', '8 В' => '5f732362f0dfa3606717b930',
        ),
        6 => array(
            '6 Г' => '5f782e6132089152aa865244',
            '7 Б' => '5f7b5e113b82873c8dd21b90',
            '8 В' => '5f782ec7a74ee319e16f57f8', '8 Г' => '5f782ecb5f6c5160f72bee00',
        ),
        7 => array(
            '6 Г' => '5f815f09c9776c1609631271',
            '7 Б' => '5f815f69ba63598a87c2e007', '7 Г' => '5f86f7d0bea8f8238f59e7fd',
            '8 В' => '5f815fecaec1d9105c601b1f',
            '9 А' => '5f86d96e7af4386409d86bcc', '9 Г' => '5f8437a91ddfe5319b798912',
            '10 В' => '5f843b42cceac925a103b922',
        ),
        8 => array(
            '6 Г' => '5f8ad2c44bc50e849cb0aca6',
            '7 Б' => '5f8ad2d3884bcc53da701263', '7 Г' => '5f8ad2d787979a0e2dbf4a0a',
            '8 В' => '5f8ad3067dd22077bf469cb0',
            '9 А' => '5f8ad322452ce006859aaae1',
            '10 В' => '5f8ad334836b01624b591cba',
        ),
        10 => array(
            '1 А' => '5f9eabe3097f7303eda982f6', '1 Б' => '5f9eabe42fa0de0347333bce', '1 В' => '5f9eabf54ec59d63ee835c1c', '1 Г' => '5f9eabf7b276b860d4cff260', '1 Д' => '5f9eabf95235ee62248f6fcb',
            '2 А' => '5f9bf2e8796d5252c04e64c1', '2 Б' => '5f9bf2eb5c195b8d0df8a6ee', '2 В' => '5f9bf2ed085d6a20c591aef5', '2 Г' => '5f9bf2efad36900d18d8c664', '2 Д' => '5f9bf2f144ec3473f0a55db8', '2 Е' => '5f9bf2f34f7b39151b1ad09b',
            '3 А' => '5f9bf3af4cefac39c14d888d', '3 Б' => '5f9bf3b1fcc22c39dc5d1165', '3 В' => '5f9bf3b3bf7ca08d7ebb4e05', '3 Г' => '5f9bf3b5eac98466d6da208e',
            '4 А' => '5f9bf4289acb5905789e419b', '4 Б' => '5f9bf42afbdda1828a78942c', '4 В' => '5f9bf42c5727da2746e68698', '4 Г' => '5f9bf42ec888ee8d0c2f0b49',
            '5 А' => '5f9bf48832576338faf503e4', '5 Б' => '5f9bf48a4a8d3833492baad4', '5 В' => '5f9bf48c83fc557ae2cf6c90', '5 Г' => '5f9bf48e10f16a8235d97777', '5 Д' => '5f9d3f8eeb4b7e312056adcf',
            '6 А' => '5f9bf53bc487400e209247d2', '6 Б' => '5f9bf53e118d3b78d72685eb', '6 В' => '5f9bf54097b9c1344f7ab17b', '6 Г' => '5f9bf5427429295f1acaeaff', '6 Д' => '5f9bf54482e090524b0f3024',
            '7 А' => '5f9bf5a6c224c00575ec4bba', '7 Б' => '5f9bf5a8520a2d621b762f17', '7 В' => '5f9bf5aa941fe03d346daec6', '7 Г' => '5f9bf5ac2464844fc65c5823',
            '8 А' => '5f9bf5f51ceec92594ed344e', '8 Б' => '5f9bf5f69aa656596e65084e', '8 В' => '5f9bf5fa432b236ecca25604', '8 Г' => '5f9bf5fc80a93121a93e2447',
            '9 А' => '5f9bf75b69f34731277d662e', '9 Б' => '5f9bf75d3a86d5050ff977bd', '9 В' => '5f9bf75fd3226a789bcaeeec', '9 Г' => '5f9bf761e03f966dd3abb819',
            '10 А' => '5f9bf7fc6cf35033cca8abd4', '10 Б' => '5f9bf7fe893e2e5fe2fad481', '10 В' => '5f9bf80044761c6eb08ea1f5', '10 Г' => '5f9bf802c40ca338dd59a769',
            '11 А' => '5f9bf9d8d359b9218de73a2e', '11 Б' => '5f9bf9da234bb55025482d28', '11 В' => '5f9bf9dcbb1e1974652a68f3', '11 Г' => '5f9bf9de5ca5ec05a301204a'
        ),
        11 => array(
            '5 А' => '5fa3f01af360e636bf189857', '5 Б' => '5fa3f01f03dab036cba9c768', '5 В' => '5fa3f0238ec10f29598f9407', '5 Г' => '5fa3f02590632659c63b1017', '5 Д' => '5fa3f0295fa39880028eb947',
            '6 А' => '5fa3dfca99e3d52d387e97df', '6 Б' => '5fa3dfcc97ccb68a79b84295', '6 В' => '5fa3dfcf633f9e7c6ae55b0e', '6 Г' => '5fa3dfd3864e7e8b45521113', '6 Д' => '5fa3e01182a3313a8b029195',
            '7 А' => '5fa3e02b4ced45558107c9d2', '7 Б' => '5fa3e032c06f6b2c233b1b62', '7 В' => '5fa3e034ba2d542886d83b80', '7 Г' => '5fa3e0360f86020994be0cc1',
            '8 А' => '5fa3e0635cdbc0483b3338df', '8 Б' => '5fa3e0650d148979e73877b0', '8 В' => '5fa3e06795f27403342b824d', '8 Г' => '5fa3e069be9e4a02791bec01',
            '9 А' => '5fa3e09340874e8350f45b48', '9 Б' => '5fa3e095d91b3c18d902fd08', '9 В' => '5fa3e097dfa5bf7659802151', '9 Г' => '5fa3e099a8d04263c33b1aea',
            '10 А' => '5fa3e0c227f4d40e42ffa4cb', '10 Б' => '5fa3e0c444b7030e3727ec13', '10 В' => '5fa3e0c6438145020401a6f5', '10 Г' => '5fa3e0c8dc46d60758cd297f',
            '11 А' => '5fa3e12faa3d518af9ae8e7a', '11 Б' => '5fa3e131e6f0687e3fa3edfb', '11 В' => '5fa3e1338291c66aea2fe980', '11 Г' => '5fa3e135f5e8db747c8403b4'
        ),
        12 => array(
            '6 А' => '5fae360ec251dd69411dc44f', '6 Б' => '5fae361241324f78099964b4', '6 В' => '5fae3614d209265be72ee722', '6 Г' => '5fae361756f3450c8ccf2f66', '6 Д' => '5fae364c19273c7a190b9d0d',
            '7 А' => '5fae36604f778f11724f8b59', '7 Б' => '5fae3662752f517855df1599', '7 В' => '5fae366492c4e809dbf74535', '7 Г' => '5fae3665a9c5c810a1bf2a6c',
            '8 А' => '5fae368c4e83320fbe42cad7', '8 Б' => '5fae368ec5f2d76b02cde828', '8 В' => '5fae36913f50f36aad3e6159', '8 Г' => '5fae3693f667487c4f347fb7',
            '9 А' => '5fae36bc6aee355aed6a7a2b', '9 Б' => '5fae36be2642dc2d414b32ff', '9 В' => '5fae36c07274891916962f66', '9 Г' => '5fae36c204f38f791bbb4dfa',
            '10 А' => '5fae36d48d1e46100d3cdc37', '10 Б' => '5fae36d779f43d803ab4ced8', '10 В' => '5fae36d98157207e80762b63', '10 Г' => '5fae36e3fb19c7782afcc34e',
            '11 А' => '5fae36fa0cc1cd70bd5e4033', '11 Б' => '5fae36fc7dbfb81003e41d0a', '11 В' => '5fae36fed61bff4aa52f7f54', '11 Г' => '5fae37009251e03fc933ea81'
        ),
        13 => array(
            '6 А' => '5fb7d6e8ac24c683b5bc756c', '6 Б' => '5fb7d6f1e60ced6137e34982', '6 В' => '5fb7d6f4a2a4fc0ae2e4935f', '6 Г' => '5fb7d6f728dcd21386a8e819', '6 Д' => '5fb7d6fae47a1e748b85ff17',
            '7 А' => '5fb7d7bd63a1c8596dcbbac8', '7 Б' => '5fb7d7bf23145972b49be0f5', '7 В' => '5fb7d7c166a3d14220f6b9dd', '7 Г' => '5fb7d7c356fbef1b909b0514',
            '8 А' => '5fb7d84517558d22a34a78b7', '8 Б' => '5fb7d847aa708c806704dbfb', '8 В' => '5fb7d855c52e0183549cbd4a', '8 Г' => '5fb7d8583e999d6187895eb7',
            '9 А' => '5fb7d90d300064663ac23169', '9 Б' => '5fb7d90f1a249a21b4b6d80b', '9 В' => '5fb7d9108485586188e97e25', '9 Г' => '5fb7d9132e861f7ba802cdb6',
            '10 А' => '5fb7d9961cdedc174b42f323', '10 Б' => '5fb7d998f4397e0b192b4a5f', '10 В' => '5fb7d99a8f9c3443ad63a67e', '10 Г' => '5fb7d99c6b1c7181728c1e53',
            '11 А' => '5fb7d9fbf34c552848590817', '11 Б' => '5fb7d9fdb8bacb0a7adb092e', '11 В' => '5fb7d9ff0773d97ed9757272', '11 Г' => '5fb7da0124474178c3e163d7'
        ),
        14 => array(
            '1 А' => '5fbcb118f93f5942208fca76',
            '6 А' => '5fb7d70e73fcc109aeda084b', '6 Б' => '5fb7d7110c5fbd671ec1bc83', '6 В' => '5fb7d7141c424c8144df1643', '6 Г' => '5fb7d716e855f648f087450f', '6 Д' => '5fb7d7190445b266c949479c',
            '7 А' => '5fb7d7e85bddfd27b19b3e5f', '7 Б' => '5fb7d7ea6e5dab5906a0c557', '7 В' => '5fb7d7ec98e728020e0a6480', '7 Г' => '5fb7d7ef60a17e480bd017bb',
            '8 А' => '5fb7d8600512b04cf5009424', '8 Б' => '5fb7d8639b5d360b4b61c907', '8 В' => '5fb7d8645881364cec9ad2e1', '8 Г' => '5fb7d867d03ec979d94a4c19',
            '9 А' => '5fb7d92fb17f35600fe03db8', '9 Б' => '5fb7d931fb81af8142477918', '9 В' => '5fb7d93387d6fa543144712b', '9 Г' => '5fb7d9359309641ba0241ea2',
            '10 А' => '5fb7d9a37b2f434cccb68477', '10 Б' => '5fb7d9a5347870600c633a49', '10 В' => '5fb7d9a7ba0c7328237fb7e0', '10 Г' => '5fb7d9aab5775f1f179c5694',
            '11 А' => '5fb7da0ce549fb772ccc0edd', '11 Б' => '5fb7da0e77ccc648dda8f69a', '11 В' => '5fb7da10c4a9ad38162b800f', '11 Г' => '5fb7da13598a684c1d717765'
        ),
        15 => array(
            '6 А' => '5fc9fe6b4966547d7caf0792', '6 Б' => '5fc9fe6d2da4b84cca02d932', '6 В' => '5fc9fe6f0d9c308f00afad48', '6 Г' => '5fc9fe71b2065981f64374ea', '6 Д' => '5fc9ff25883f103bc3155f4e',
            '7 А' => '5fca0352af1ce081a5525fba', '7 Б' => '5fca0355925e90726a5300c9', '7 В' => '5fca03580c17736d7b652e0d', '7 Г' => '5fca035a095db27a149e12a6',
            '8 А' => '5fca038e69fe0d828e20ab8d', '8 Б' => '5fca038f85bc2f10ac398def', '8 В' => '5fca0392afaf8f68c9f748c4', '8 Г' => '5fca03949a12135c78013f37',
            '9 А' => '5fca03cee25cc418d31914db', '9 Б' => '5fca03d1341856507ad60f46', '9 В' => '5fca03d4c2119b27c2d4ea44', '9 Г' => '5fca03d6a8463287c7b5fbd1',
            '10 А' => '5fca041f47d8e28eca106a05', '10 Б' => '5fca042179513f5ef1acf795', '10 В' => '5fca04249341e4593c79e0dc', '10 Г' => '5fca042624c70c7409340cfb',
            '11 А' => '5fca04ed0b8c1f24ef1b617d', '11 Б' => '5fca04ef80e4518aed9acc35', '11 В' => '5fca04f109aa9f4b4808d5df', '11 Г' => '5fca04f462b7d319f7e2ca95'
        ),
        16 => array(
            '6 А' => '5fd24908f0ca5d09b3fa43aa', '6 Б' => '5fd2490c18c5466bdc240e50', '6 В' => '5fd2490f5a6d567810434e6a', '6 Г' => '5fd24911be75f78c12bc8b0e', '6 Д' => '5fd24a9e7f329f54fb497168',
            '7 А' => '5fd249e6ad449b0b6a5f2f39', '7 Б' => '5fd249e894b133182568d420', '7 В' => '5fd249ea751562099a932100', '7 Г' => '5fd249edd582ac37090e60b7',
            '8 А' => '5fd24b1285d6280a577dd462', '8 Б' => '5fd24b1500c0f9246f0eac9d', '8 В' => '5fd24b179b4b4e808414d878', '8 Г' => '5fd24b1a3d2e586393d1564a',
            '9 А' => '5fd24b7580827e3669fd4a83', '9 Б' => '5fd24b78ba3fd1274e917cbd', '9 В' => '5fd24b7a723eed76f07ee375', '9 Г' => '5fd24b7d8575150a1301762a',
            '10 А' => '5fd24be75b6c271d6145c188', '10 Б' => '5fd24be9a3a0587f861d4706', '10 В' => '5fd24bec3e062e09d361f3ea', '10 Г' => '5fd24bee5fab4b85b0a26b46',
            '11 А' => '5fd24c38582a714c5865f4d6', '11 Б' => '5fd24c3a54c4bd5b17684f55', '11 В' => '5fd24c3ce999456edbdda762', '11 Г' => '5fd24c3fed94d66f4dfb422a'
        ),
        17 => array(
            '6 А' => '5fdc4ed56cfda687051d252d', '6 Б' => '5fdc4ed9ad859229de5483e9', '6 В' => '5fdc4edc18b65e2061068db9', '6 Г' => '5fdc4edf8085ac6d87185b09', '6 Д' => '5fdc4ee2f71fa764f6630e6e',
            '7 А' => '5fdc4f2c02c8d75c3b1b5caa', '7 Б' => '5fdc4f2e6512a70baabe149a', '7 В' => '5fdc4f31b361d16861dcf3c3', '7 Г' => '5fdc4f3379984e6c14bdbdc0',
            '8 А' => '5fdc4f8d1a56226932f20919', '8 Б' => '5fdc4f8fe0b3fc7cbd7df88e', '8 В' => '5fdc4f911145182195a87e51', '8 Г' => '5fdc4f949d43e13de0bb6fdb',
            '9 А' => '5fdc4fcf9a23648365e6b96a', '9 Б' => '5fdc4fd19dbccc051cb14f44', '9 В' => '5fdc4fd4098f3d03f25b1ae9', '9 Г' => '5fdc4fd63bf783803e762140',
            '10 А' => '5fdc5015eb627b74dee4071c', '10 Б' => '5fdc5018369ac86945c9b5a0', '10 В' => '5fdc501a51d5f74aa556d932', '10 Г' => '5fdc501c8875536b82901527',
            '11 А' => '5fdc504fac353e74de0c1168', '11 Б' => '5fdc50523b2f632f45074f8a', '11 В' => '5fdc5054b2ddd186ebddc5ac', '11 Г' => '5fdc50574d7b1439beb9a6cf'
        ),
        18 => array(
            '6 А' => '5fe6e5867c2fdc43b890fbcd', '6 Б' => '5fe6e58880102e214bb0b9a4', '6 В' => '5fe6e58aa5a5ce493b689ef2', '6 Г' => '5fe6e58c5e7f7b439d5f1af5', '6 Д' => '5fe6e58ec0b430523090e108',
            '7 А' => '5fe6e5e9bbc767442a3df369', '7 Б' => '5fe6e5eb720e0b44d6552486', '7 В' => '5fe6e5ed3aed1a3ed43cd550', '7 Г' => '5fe6e5efee6c9b18b795a998',
            '8 А' => '5fe6e64fc39bc50bd85b99a8', '8 Б' => '5fe6e6518ff85b699e1c89db', '8 В' => '5fe6e65357b5840f61eff998', '8 Г' => '5fe6e6556d20d48e050f8f7e',
            '9 А' => '5fe6e6ba4a32ff2d71288747', '9 Б' => '5fe6e6bcadfaec81b9d0ccdf', '9 В' => '5fe6e6bd4884292dfd3b5b64', '9 Г' => '5fe6e6bf9159d5512ddc24b6',
            '10 А' => '5fe6e713ba309c465bb1235a', '10 Б' => '5fe6e715ee5c440867e9114f', '10 В' => '5fe6e7176522804157b30371', '10 Г' => '5fe6e719019f5e8244c07fb2',
            '11 А' => '5fe6e7674dfc7c458215fc9e', '11 Б' => '5fe6e76abedb8402e9b24436', '11 В' => '5fe6e76de2a817413ccf0b7d', '11 Г' => '5fe6e771ef84285e09ce1293'
        ),
        20 => array(
            '6 А' => '5fe6e7f7e1ec324289a9c335', '6 Б' => '5fe6e7fb344bc51f3129e135', '6 В' => '5fe6e7fd48170133a5cf0f7a', '6 Г' => '5fe6e7ff081a1d7f8cf2e761', '6 Д' => '5fe6e801476d8474a2d617ad',
            '7 А' => '5fe6e815d4566b3eaac97aad', '7 Б' => '5fe6e81798e5133d8a5e1a46', '7 В' => '5fe6e819323e3417635472b0', '7 Г' => '5fe6e81be9b5b750b785caab',
            '8 А' => '5fe6e82df201737d9c4205b1', '8 Б' => '5fe6e8302c67266b37172dfa', '8 В' => '5fe6e832a85ff80eb7c8a2fe', '8 Г' => '5fe6e834dae9ad59d7aed3b5',
            '9 А' => '5fe6e83fcc9bba75b0fbb3e7', '9 Б' => '5fe6e841e5686e5a461179e9', '9 В' => '5fe6e8437d231d69d8357dae', '9 Г' => '5fe6e845e8f04903cd0cfe38',
            '10 А' => '5fe6e852f405046df83470aa', '10 Б' => '5fe6e85412e5107099787df0', '10 В' => '5fe6e8562fd5fe70461e7d11', '10 Г' => '5fe6e859e424d01c72fc2187',
            '11 А' => '5fe6e865cd4ea078da77281f', '11 Б' => '5fe6e86737ff715e9226c6c8', '11 В' => '5fe6e86994fa23120a2ca330', '11 Г' => '5fe6e86b2f76923d767a9b38'
        ),
    );

    public static $boardIds = array(
        1 => '5e84908288998f4bc0162576',
        2 => '5e8490615ce1dc1f62755847',
        3 => '5e8490a4f7623a3391f25573',
        4 => '5e8490b46e12625566881223',
        5 => '5e84909554059625212bd682',
        6 => '5e84913fea0a03324a67d330',
        7 => '5e849183f40cf6677629c477',
        8 => '5e84919549e82b4b15d0f84e',
        9 => '5e8491a5d05b0136de001dee',
        10 => '5e8491b0d6b2a584af21fbea',
        11 => '5e8491bfd99d19471c0d2388'
    );

    public function index() {
        $css = Carbon::createFromFormat("Y-m-d", ConfigOption::SemesterStarts())->startOfWeek();

        $faculties = Faculty::all()->sortBy('sorting_order');
        $weekCount = Calendar::WeekCount();

        $weeks = array();
        for($w = 1; $w <= $weekCount; $w++) {
            $start = $css->clone();
            $end = $start->clone()->addDays(6);
            $weeks[$w] = $start->format("d.m") . " - " . $end->format('d.m');

            $css = $css->addWeek();
        }

        $today = CarbonImmutable::now()->format('Y-m-d');
        $css = Carbon::createFromFormat("Y-m-d", ConfigOption::SemesterStarts())->startOfWeek();
        $currentWeek = Calendar::WeekFromDate($today, $css);

        return view('trello.indexNew', compact('faculties', 'weekCount', 'weeks', 'currentWeek'));
    }

    public function upload(Request $request) {
        $input = $request->all();

        $facultyId = $input["facultyId"];
        $week = $input["week"];
        $dows = explode('|', $input['dows']);
        sort($dows);

        $trelloListIds = TrelloController::$trelloListIds[$week];

        $calendarIds = Calendar::IdsFromDowsAndWeek($dows, $week);

        $facultyGroupIds = DB::table('faculty_student_group')
            ->where('faculty_id', '=', $facultyId)
            ->select('student_group_id')
            ->get()
            ->map(function($item) { return $item->student_group_id;})
            ->toArray();

        $result = array();

        foreach ($facultyGroupIds as $facultyGroupId) {
            $groupName = StudentGroup::find($facultyGroupId)->name;
            if (!array_key_exists($groupName, $trelloListIds)) {
                continue;
            }
            $trelloListId = $trelloListIds[$groupName];
            $result[$trelloListId] = array();

            $studentIds = DB::table('student_student_group')
                ->where('student_group_id', '=', $facultyGroupId)
                ->select('student_id')
                ->get()
                ->map(function($item) { return $item->student_id;});

            $groupExtendedIds = DB::table('student_student_group')
                ->whereIn('student_id', $studentIds)
                ->select('student_group_id')
                ->get()
                ->map(function($item) { return $item->student_group_id;})
                ->unique();

            $weekFacultyLessons = DB::table('lessons')
                ->join('discipline_teacher', 'lessons.discipline_teacher_id', '=', 'discipline_teacher.id')
                ->join('teachers', 'discipline_teacher.teacher_id', '=', 'teachers.id')
                ->join('disciplines', 'discipline_teacher.discipline_id', '=', 'disciplines.id')
                ->join('student_groups', 'disciplines.student_group_id', '=', 'student_groups.id')
                ->join('calendars', 'lessons.calendar_id', '=', 'calendars.id')
                ->join('rings', 'lessons.ring_id', '=', 'rings.id')
                ->join('auditoriums', 'lessons.auditorium_id', '=', 'auditoriums.id')
                ->where('lessons.state', '=', 1)
                ->wherein('student_groups.id', $groupExtendedIds)
                ->whereIn('lessons.calendar_id', $calendarIds)
                ->select('lessons.id as lessonsId',
                    'rings.time as ringsTime', 'disciplines.name as disciplinesName',
                    'student_groups.name as studentGroupsName', 'teachers.fio as teachersFio',
                    'calendars.date as calendarsDate'
                )
                ->get();

            foreach ($weekFacultyLessons as $key => $lesson) {
                $lessonItem = array();

                $fioSplit = explode(' ', $lesson->teachersFio);
                $teacherFio = $fioSplit[0] . " " . mb_substr($fioSplit[1], 0, 1) . "." . mb_substr($fioSplit[2], 0, 1) . ".";

                $carbonDate = Carbon::createFromFormat('Y-m-d', $lesson->calendarsDate);
                $dowRu = array( 1 => "Пн", 2 => "Вт", 3 => "Ср", 4 => "Чт", 5 => "Пт", 6 => "Сб", 7 => "Вс");
                $dow = $carbonDate->dayOfWeekIso;
                $carbonDateDM = $carbonDate->format("d.m");


                $lessonItem['name'] = $carbonDateDM . " " . $dowRu[$dow] . " " .
                    mb_substr($lesson->ringsTime, 0, 5) . " - " . $lesson->disciplinesName .
                    " (" .$lesson->studentGroupsName . ") - " . $teacherFio;
                $h = intval(mb_substr($lesson->ringsTime, 0, 2));
                $utcH = $h - 4;
                if ($utcH < 10) $utcH = "0" . $utcH;



                $lessonItem['due'] = $lesson->calendarsDate . "T" . $utcH . mb_substr($lesson->ringsTime, 2, 3) . ":00.000Z";

                $result[$trelloListId][] = $lessonItem;
            }
        }

        foreach ($result as $listId => $lessons) {
            usort($lessons, function ($a, $b) {
                $ad = new DateTime($a['due']);
                $bd = new DateTime($b['due']);

                if ($ad == $bd) {
                    return 0;
                }

                return $ad < $bd ? -1 : 1;
            });

            $result[$listId] = $lessons;
        }

        //return $result;

        $stack = HandlerStack::create();
        $middleware = new Oauth1([
            'consumer_key'    => 'a8c89955d4d62ad9bd2f50c304d3dd9d',
            'consumer_secret' => 'bd7e2095b3013b1a70f435f5ff936c6ded7503d9bfec351b89f480eddc6702cf',
            'token'           => 'f41efa8a36f62ce93bcd4b0aee9777ccc0e4dac326840cd6c2caf9df3f586153',
            'token_secret'    => 'bd7e2095b3013b1a70f435f5ff936c6ded7503d9bfec351b89f480eddc6702cf'
        ]);
        $stack->push($middleware);
        $client = new Client([
            'base_uri' => 'https://api.trello.com/1/',
            'handler' => $stack,
            'auth' => 'oauth'
        ]);

        $siteCards = array();
        foreach ($result as $listId => $lessons) {
            $res = $client->get('lists/' . $listId .'/cards');
            $data = json_decode($res->getBody());
            $siteCards[$listId] = $data;
        }

        //dd($result, $siteCards);

        foreach ($result as $listId => $lessons) {
            foreach ($lessons as $lesson) {
                $cardExists = false;
                foreach($siteCards[$listId] as $cardLesson) {
                    if ($lesson['name'] == $cardLesson->name) {
                        $cardExists = true;
                        break;
                    }
                }

                if (!$cardExists) {
                    $res = $client->post('cards', [
                        'query' => [
                            'idList' => $listId, // 5e8494347ea6d63682b9856f
                            'name' => $lesson['name'], // 06.04 Пн 08:30 - Математика (1 А) - Манурина В.А.
                            'due' => $lesson['due'], // 2020-04-06T04:30:00.000Z
                        ]
                    ]);
                }
            }
        }
    }

    public function brb()
    {
        $teachers = Teacher::all()->sortBy('fio');
        foreach ($teachers as $teacher) {
            if ($teacher->user_id == null) {
                $pass = "";
                $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $charactersLength = strlen($characters);
                $randomString = '';
                for ($i = 0; $i < 12; $i++) {
                    $pass .= $characters[rand(0, $charactersLength - 1)];
                }

                $cyr = [
                    'а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п',
                    'р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я',
                    'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П',
                    'Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я'
                ];
                $lat = [
                    'a','b','v','g','d','e','io','zh','z','i','y','k','l','m','n','o','p',
                    'r','s','t','u','f','h','ts','ch','sh','sht','a','i','y','e','yu','ya',
                    'A','B','V','G','D','E','Io','Zh','Z','I','Y','K','L','M','N','O','P',
                    'R','S','T','U','F','H','Ts','Ch','Sh','Sht','A','I','Y','e','Yu','Ya'
                ];

                $fiowospaces = str_replace(" ", "", $teacher->fio);
                $name_lat = str_replace($cyr, $lat, $fiowospaces);
                $email = $name_lat . "@nayanova.edu";

                $teacher->pass = $pass;
                $teacher->email = $email;

                $user = new User();
                $user->password = Hash::make($pass);
                $user->email = $email;
                $user->name = $teacher->fio;
                $user->save();

                $t = Teacher::find($teacher->id);
                $t->user_id = $user->id;
                $t->save();
            }
        }
        return $teachers;


//        $user = new User();
//        $user->password = Hash::make('thebest');
//        $user->email = 'Lyudmilaalex.ki@gmail.com';
//        $user->name = 'Кузнецова Людмила';
//        $user->save();
//        return "OK";

//        $file = fopen(storage_path("data.txt"), "r");
//        while(!feof($file)) {
//            $line = fgets($file);
//            $explode = explode('@', $line);
//            if (count($explode)== 2) {
//                $newTeacher = new Teacher();
//                $newTeacher->fio = $explode[0];
//                $newTeacher->phone = $explode[1];
//                $newTeacher->save();
//            }
//        }
//        fclose($file);
//

    }

    public function checkIndex() {
        $css = Carbon::createFromFormat("Y-m-d", ConfigOption::SemesterStarts())->startOfWeek();

        $faculties = Faculty::all()->sortBy('sorting_order');
        $weekCount = Calendar::WeekCount();

        $weeks = array (1 => "31.08 - 06.09", 2 => "07.09 - 13.09", 3 => "14.09 - 20.09",
            4 => "21.09 - 27.09", 5 => "28.09 - 04.10", 6 => "05.10 - 11.10",
            7 => "12.10 - 18.10", 8 => "19.10 - 25.10", 9 => "26.10 - 01.11",
            10 => "02.11 - 08.11", 11 => "09.11 - 15.11", 12 => "16.11 - 22.11",
            13 => "23.11 - 29.11", 14 => "30.11 - 06.12", 15 => "07.11 - 13.12",
            16 => "14.11 - 20.12", 17 => "21.11 - 27.12",
            );

        $today = CarbonImmutable::now()->format('Y-m-d');
        $css = Carbon::createFromFormat("Y-m-d", ConfigOption::SemesterStarts())->startOfWeek();
        $currentWeek = Calendar::WeekFromDate($today, $css);

        return view('trello.check', compact('faculties', 'weekCount', 'weeks', 'currentWeek'));
    }

    public function checkAction(Request $request) {
        $input = $request->all();

        $facultyId = $input["facultyId"];
        $week = $input["week"];
        $dows = explode('|', $input['dows']);
        sort($dows);


        $stack = HandlerStack::create();
        $middleware = new Oauth1([
            'consumer_key'    => 'a8c89955d4d62ad9bd2f50c304d3dd9d',
            'consumer_secret' => 'bd7e2095b3013b1a70f435f5ff936c6ded7503d9bfec351b89f480eddc6702cf',
            'token'           => 'f41efa8a36f62ce93bcd4b0aee9777ccc0e4dac326840cd6c2caf9df3f586153',
            'token_secret'    => 'bd7e2095b3013b1a70f435f5ff936c6ded7503d9bfec351b89f480eddc6702cf'
        ]);
        $stack->push($middleware);
        $client = new Client([
            'base_uri' => 'https://api.trello.com/1/',
            'handler' => $stack,
            'auth' => 'oauth'
        ]);

        $calendarIds = Calendar::IdsFromWeek($week);

        $result = array();

        $trelloWeekListIds = TrelloController::$trelloListIds[$week];

        foreach ($trelloWeekListIds as $groupName => $listId) {
            $grade = explode(' ', $groupName)[0];
            if ($grade !== $facultyId) {
                continue;
            }
            $facultyGroupId = StudentGroup::IdFromName($groupName);

            $studentIds = DB::table('student_student_group')
                ->where('student_group_id', '=', $facultyGroupId)
                ->select('student_id')
                ->get()
                ->map(function($item) { return $item->student_id;});

            $groupExtendedIds = DB::table('student_student_group')
                ->whereIn('student_id', $studentIds)
                ->select('student_group_id')
                ->get()
                ->map(function($item) { return $item->student_group_id;})
                ->unique();

            $groupNames = DB::table('student_groups')
                ->whereIn('id', $groupExtendedIds)
                ->get()
                ->pluck('name');

            $weekGroupLessons = DB::table('lessons')
                ->join('discipline_teacher', 'lessons.discipline_teacher_id', '=', 'discipline_teacher.id')
                ->join('teachers', 'discipline_teacher.teacher_id', '=', 'teachers.id')
                ->join('disciplines', 'discipline_teacher.discipline_id', '=', 'disciplines.id')
                ->join('student_groups', 'disciplines.student_group_id', '=', 'student_groups.id')
                ->join('calendars', 'lessons.calendar_id', '=', 'calendars.id')
                ->join('rings', 'lessons.ring_id', '=', 'rings.id')
                ->join('auditoriums', 'lessons.auditorium_id', '=', 'auditoriums.id')
                ->where('lessons.state', '=', 1)
                ->wherein('student_groups.id', $groupExtendedIds)
                ->whereIn('lessons.calendar_id', $calendarIds)
                ->select('lessons.id as lessonsId',
                    'rings.time as ringsTime', 'disciplines.name as disciplinesName',
                    'student_groups.name as studentGroupsName', 'teachers.fio as teachersFio',
                    'calendars.date as calendarsDate'
                )
                ->get();
            $lessonList = array();

            foreach ($weekGroupLessons as $key => $lesson) {
                $lessonItem = array();

                $fioSplit = explode(' ', $lesson->teachersFio);
                $teacherFio = $fioSplit[0] . " " . mb_substr($fioSplit[1], 0, 1) . "." . mb_substr($fioSplit[2], 0, 1) . ".";

                $carbonDate = Carbon::createFromFormat('Y-m-d', $lesson->calendarsDate);
                $dowRu = array( 1 => "Пн", 2 => "Вт", 3 => "Ср", 4 => "Чт", 5 => "Пт", 6 => "Сб", 7 => "Вс");
                $dow = $carbonDate->dayOfWeekIso;
                $carbonDateDM = $carbonDate->format("d.m");


                $lessonItem['name'] = $carbonDateDM . " " . $dowRu[$dow] . " " .
                    mb_substr($lesson->ringsTime, 0, 5) . " - " . $lesson->disciplinesName .
                    " (" .$lesson->studentGroupsName . ") - " . $teacherFio;
                $h = intval(mb_substr($lesson->ringsTime, 0, 2));
                $utcH = $h - 4;
                if ($utcH < 10) $utcH = "0" . $utcH;



                $lessonItem['due'] = $lesson->calendarsDate . "T" . $utcH . mb_substr($lesson->ringsTime, 2, 3) . ":00.000Z";

                $lessonList[] = $lessonItem;
            }


            $res = $client->get('lists/' . $listId .'/cards');
            $data = json_decode($res->getBody());

            //dd($lessonList, $data);

            foreach ($lessonList as $lesson) {
                $found = false;
                $sameName = null;
                foreach ($data as $dataLesson) {
                    if ($lesson['name'] === $dataLesson->name && $lesson['due'] === $dataLesson->due) {
                        $found = true;
                        break;
                    }

                    if ($lesson['name'] === $dataLesson->name) {
                        if (is_null($sameName)) $sameName = array();
                        $sameName[] = array(
                            'wiki' => $lesson,
                            'trello' => $dataLesson
                        );
                    }
                }

                if (!$found && is_null($sameName)) {
                    $item = array();
                    $item["name"] = $lesson['name'];
                    $item["due"] = $lesson['due'];
                    $item["description"] = "В Trello нет карточки урока из расписания";
                    $item["url"] = "";
                    $result[] = $item;
                }

                if (!$found && !is_null($sameName)) {
                    $trelloLessonsDue = array_column(array_column($sameName, 'trello'), 'due');
                    $trelloLessonsDueFormat = array_map(function ($dt) {
                        $carbonDate = Carbon::createFromTimestamp(strtotime($dt));
                        return $carbonDate->format('d.m.Y H:i');
                    }, $trelloLessonsDue);

                    $item = array();
                    $item["name"] = $lesson['name'];
                    $item["due"] = $lesson['due'];
                    $item["description"] = "В Trello в карточке другое время: " . implode(' / ', $trelloLessonsDueFormat);
                    $item["url"] = "";
                    $result[] = $item;
                }
            }

            foreach ($data as $dataLesson) {
                $found = false;
                $sameName = null;
                foreach ($lessonList as $lesson) {
                    if ($dataLesson->name === $lesson['name'] && $dataLesson->due === $lesson['due']) {
                        $found = true;
                        break;
                    }

                    if ($dataLesson->name === $lesson['name']) {
                        if (is_null($sameName)) $sameName = array();
                        $sameName[] = array(
                            'trello' => $dataLesson,
                            'wiki' => $lesson
                        );
                    }
                }

                if (!$found && is_null($sameName)) {
                    $item = array();
                    $item["name"] = $dataLesson->name;
                    $item["due"] = $dataLesson->due;
                    $item["description"] = "В Trello есть карточка не соответствующая расписанию";
                    $item["url"] = $dataLesson->url;
                    $result[] = $item;
                }

                if (!$found && !is_null($sameName)) {
                    $wikiLessonsDue = array_column(array_column($sameName, 'wiki'), 'due');
                    $wikiLessonsDueFormat = array_map(function ($dt) {
                        $carbonDate = Carbon::createFromTimestamp(strtotime($dt));
                        return $carbonDate->format('d.m.Y H:i');
                    }, $wikiLessonsDue);
                    $trelloLessonsDue = array_column(array_column($sameName, 'trello'), 'due');
                    $trelloLessonsDueFormat = array_map(function ($dt) {
                        $carbonDate = Carbon::createFromTimestamp(strtotime($dt));
                        return $carbonDate->format('d.m.Y H:i');
                    }, $trelloLessonsDue);
                    $timeString = '';
                    for ($i = 0; $i < count($wikiLessonsDueFormat); $i++) {
                        $timeString .= $wikiLessonsDueFormat[$i] . " (расписание) - " . $trelloLessonsDueFormat[$i];
                        if ($i !== count($wikiLessonsDueFormat) - 1) {
                            $timeString .= ' / ';
                        }
                    }
                    $item = array();
                    $item["name"] = $dataLesson->name;
                    $item["due"] = $dataLesson->due;
                    $item["description"] = "В расписании время отличается от Trello: " . $timeString;
                    $item["url"] = $dataLesson->url;
                    $result[] = $item;
                }
            }

            foreach ($data as $cardData) {
                $cardDate = ""; $carbonDate = ""; $dow = 8;
                if ($cardData->due !== null) {
                    $cardDate = mb_substr($cardData->due, 0, 10) . " " . mb_substr($cardData->due, 11, 8);
                    $carbonDate = Carbon::createFromFormat('Y-m-d H:i:s', $cardDate)->addMinutes(240);
                    $dowRu = array(1 => "Пн", 2 => "Вт", 3 => "Ср", 4 => "Чт", 5 => "Пт", 6 => "Сб", 7 => "Вс");
                    $dow = $carbonDate->dayOfWeekIso;
                }

                if (in_array($dow, $dows) || $dow == 8) {
                    if ($cardData->due !== null) {
                        $descriptionFillDeadlineTime = $carbonDate->copy()->subDays(3);
                        $descriptionFillDeadlineTime->setTime(11, 0, 0);
                    }
                    if ($cardData->desc == "" && (true || (($dow == 8) || (Carbon::now()->gt($descriptionFillDeadlineTime))))) {
                        $item = array();
                        $item["name"] = $cardData->name;
                        $item["description"] = "Описание пустое";
                        $item["url"] = $cardData->url;
                        $result[] = $item;
                    }

                    if (($dow !== 8) && (Carbon::now()->gt($carbonDate->copy()->addMinutes(40)))) {
                        if ($cardData->dueComplete == false) {
                            $item = array();
                            $item["name"] = $cardData->name;
                            $item["description"] = "Нет отметки о проведении урока";
                            $item["url"] = $cardData->url;
                            $result[] = $item;
                        }
                    }

                    if (($dow !== 8) && ((Carbon::now()->lt($carbonDate->copy())))) {
                        if ($cardData->dueComplete == true) {
                            $item = array();
                            $item["name"] = $cardData->name;
                            $item["description"] = "Отметка о выполнении проставлена до начала урока";
                            $item["url"] = $cardData->url;
                            $result[] = $item;
                        }
                    }

                    $rightIndex = mb_strrpos($cardData->name, ')');
                    $leftIndex = mb_strrpos($cardData->name, '(');
                    $groupName = mb_substr($cardData->name, $leftIndex + 1, $rightIndex - $leftIndex - 1);

                    if (!$groupNames->contains($groupName)) {
                        $item = array();
                        $item["name"] = $cardData->name;
                        $item["description"] = "Группа в карточке (" . $groupName . ") не соответствует группе списка "
                            . " (" . implode(", ", $groupNames->toArray()) . ").";
                        $item["url"] = $cardData->url;
                        $result[] = $item;
                    }
                }
            }
        }

        return $result;
    }

    public function trelloDayIndex() {
        $dates = array(
            array('date' => '05.11.2020', 'week' => 10),
        );
        $groups = StudentGroup::FacultiesGroups();

        return view('trello.dayIndex', compact('dates', 'groups'));
    }

    public function  trelloDayLoadGroup(Request $request) {
        $input = $request->all();
        $date = $input['date']; //13.04.2020
        $mysqlDate = mb_substr($date, 6, 4) . '-' . mb_substr($date, 3, 2) . '-' . mb_substr($date, 0, 2);
        $groupId = $input['groupId'];
        $group = StudentGroup::find($groupId);

        $dateWeek = array(
            '05.11.2020' => 10
        );
        $week = $dateWeek[$date];

        $listId = TrelloController::$trelloListIds[$week][$group->name];

        $stack = HandlerStack::create();
        $middleware = new Oauth1([
            'consumer_key'    => 'a8c89955d4d62ad9bd2f50c304d3dd9d',
            'consumer_secret' => 'bd7e2095b3013b1a70f435f5ff936c6ded7503d9bfec351b89f480eddc6702cf',
            'token'           => 'f41efa8a36f62ce93bcd4b0aee9777ccc0e4dac326840cd6c2caf9df3f586153',
            'token_secret'    => 'bd7e2095b3013b1a70f435f5ff936c6ded7503d9bfec351b89f480eddc6702cf'
        ]);
        $stack->push($middleware);
        $client = new Client([
            'base_uri' => 'https://api.trello.com/1/',
            'handler' => $stack,
            'auth' => 'oauth'
        ]);

        $res = $client->get('lists/' . $listId .'/cards');
        $data = json_decode($res->getBody());

        $data = array_filter($data, function($lesson) use ($mysqlDate) {
            return mb_substr($lesson->due, 0, 10) == $mysqlDate;
        });

        foreach ($data as $lesson) {
            $res = $client->get('cards/' . $lesson->id . '/actions');
            $lessonData = json_decode($res->getBody());

            $lessonData = array_filter($lessonData, function($action)  {
                return $action->type == "commentCard";
            });

            $lesson->comments = $lessonData;
        }

        return $data;
    }

    public function trelloTeacherIndex() {
        $dates = array(
            '06.04.2020', '07.04.2020', '08.04.2020', '09.04.2020', '10.04.2020', '11.04.2020',
            '13.04.2020', '14.04.2020', '15.04.2020', '16.04.2020', '17.04.2020', '18.04.2020',
            '20.04.2020', '21.04.2020', '22.04.2020', '23.04.2020', '24.04.2020', '25.04.2020',
            '27.04.2020', '28.04.2020', '29.04.2020', '30.04.2020',
            '06.05.2020', '07.05.2020', '08.05.2020',
            '12.05.2020', '13.05.2020', '14.05.2020', '15.05.2020', '16.05.2020',
            '18.05.2020', '19.05.2020', '20.05.2020', '21.05.2020', '22.05.2020', '23.05.2020',
            '25.05.2020', '26.05.2020', '27.05.2020', '28.05.2020', '29.05.2020', '30.05.2020'
        );
        $teachers = Teacher::all()->sortBy('fio');

        return view('trello.dayTeacherIndex', compact('dates', 'teachers'));
    }

    public function trelloLoadTeacher(Request $request) {
        $input = $request->all();

        $date = $input['date']; //13.04.2020
        $mysqlDate = mb_substr($date, 6, 4) . '-' . mb_substr($date, 3, 2) . '-' . mb_substr($date, 0, 2);
        $teacherId = $input['teacherId'];
        $teacher = Teacher::find($teacherId);
        $fioSplit = explode(' ', $teacher->fio);
        $teacherFio = $fioSplit[0] . " " . mb_substr($fioSplit[1], 0, 1) . "." . mb_substr($fioSplit[2], 0, 1) . ".";

        $dateWeek = array(
            '06.04.2020' => 33, '07.04.2020' => 33, '08.04.2020' => 33, '09.04.2020' => 33, '10.04.2020' => 33, '11.04.2020' => 33,
            '13.04.2020' => 34, '14.04.2020' => 34, '15.04.2020' => 34, '16.04.2020' => 34, '17.04.2020' => 34, '18.04.2020' => 34,
            '20.04.2020' => 35, '21.04.2020' => 35, '22.04.2020' => 35, '23.04.2020' => 35, '24.04.2020' => 35, '25.04.2020' => 35,
            '27.04.2020' => 36, '28.04.2020' => 36, '29.04.2020' => 36, '30.04.2020' => 36,
            '06.05.2020' => 37, '07.05.2020' => 37, '08.05.2020' => 37,
            '12.05.2020' => 38, '13.05.2020' => 38, '14.05.2020' => 38, '15.05.2020' => 38, '16.05.2020' => 38,
            '18.05.2020' => 39, '19.05.2020' => 39, '20.05.2020' => 39, '21.05.2020' => 39, '22.05.2020' => 39, '23.05.2020' => 39,
            '25.05.2020' => 40, '26.05.2020' => 40, '27.05.2020' => 40, '28.05.2020' => 40, '29.05.2020' => 40, '30.05.2020' => 40
        );
        $week = $dateWeek[$date];

        $stack = HandlerStack::create();
        $middleware = new Oauth1([
            'consumer_key'    => 'a8c89955d4d62ad9bd2f50c304d3dd9d',
            'consumer_secret' => 'bd7e2095b3013b1a70f435f5ff936c6ded7503d9bfec351b89f480eddc6702cf',
            'token'           => 'f41efa8a36f62ce93bcd4b0aee9777ccc0e4dac326840cd6c2caf9df3f586153',
            'token_secret'    => 'bd7e2095b3013b1a70f435f5ff936c6ded7503d9bfec351b89f480eddc6702cf'
        ]);
        $stack->push($middleware);
        $client = new Client([
            'base_uri' => 'https://api.trello.com/1/',
            'handler' => $stack,
            'auth' => 'oauth'
        ]);

        $result = array();

        $trelloBoardIds = array_values(TrelloController::$boardIds);
        foreach ($trelloBoardIds as $boardId) {
            $res = $client->get('boards/' . $boardId .'/cards');
            $data = json_decode($res->getBody());

            $data = array_filter($data, function($lesson) use ($teacherFio, $mysqlDate) {
                return (mb_substr($lesson->due, 0, 10) == $mysqlDate) && (strpos($lesson->name, $teacherFio) !== false);
            });

            foreach ($data as $lesson) {
                $res = $client->get('cards/' . $lesson->id . '/actions');
                $lessonData = json_decode($res->getBody());

                $lessonData = array_filter($lessonData, function($action)  {
                    return $action->type == "commentCard";
                });

                $lesson->comments = $lessonData;
            }

            $result = array_merge($result, $data);
        }

        return $result;
    }

    public function trelloOnline() {
        $css = Carbon::createFromFormat("Y-m-d", ConfigOption::SemesterStarts())->startOfWeek();

        $weekCount = Calendar::WeekCount();

        $weeks = array (1 => "31.08 - 06.09", 2 => "07.09 - 13.09", 3 => "14.09 - 20.09",
            4 => "21.09 - 27.09", 5 => "28.09 - 04.10", 6 => "05.10 - 11.10",
            7 => "12.10 - 18.10", 8 => "19.10 - 25.10", 9 => "26.10 - 01.11",
            10 => "02.11 - 08.11", 11 => "09.11 - 15.11", 12 => "16.11 - 22.11",
            13 => "23.11 - 29.11", 14 => "30.11 - 06.12", 15 => "07.12 - 13.12",
            16 => "14.11 - 20.12", 17 => "21.11 - 27.12",
        );

        $today = CarbonImmutable::now()->format('Y-m-d');
        $css = Carbon::createFromFormat("Y-m-d", ConfigOption::SemesterStarts())->startOfWeek();
        $currentWeek = Calendar::WeekFromDate($today, $css);
        $faculties = Faculty::all()->sortBy('sorting_order');

        return view('trello.online', compact('weekCount', 'weeks', 'currentWeek', 'faculties'));
    }

    public function trelloOnlineAction(Request $request) {
        $input = $request->all();

        $week = $input["week"];
        $facultyId = $input["facultyId"];
        $weekDates = Calendar::CalendarsFromWeek($week)->pluck('date')->toArray();

        $stack = HandlerStack::create();
        $middleware = new Oauth1([
            'consumer_key'    => 'a8c89955d4d62ad9bd2f50c304d3dd9d',
            'consumer_secret' => 'bd7e2095b3013b1a70f435f5ff936c6ded7503d9bfec351b89f480eddc6702cf',
            'token'           => 'f41efa8a36f62ce93bcd4b0aee9777ccc0e4dac326840cd6c2caf9df3f586153',
            'token_secret'    => 'bd7e2095b3013b1a70f435f5ff936c6ded7503d9bfec351b89f480eddc6702cf'
        ]);
        $stack->push($middleware);
        $client = new Client([
            'base_uri' => 'https://api.trello.com/1/',
            'handler' => $stack,
            'auth' => 'oauth'
        ]);

        $lessonList = array();
        $result = array();

        if ($facultyId == 0 || !array_key_exists($facultyId, TrelloController::$boardIds)) {
            $trelloBoardIds = array_values(TrelloController::$boardIds);
        } else {
            $trelloBoardIds = array(TrelloController::$boardIds[$facultyId]);
        }

        foreach ($trelloBoardIds as $boardId) {
            $res = $client->get('boards/' . $boardId .'/cards');
            $data = json_decode($res->getBody());

            $data = array_filter($data, function($lesson) use ($weekDates) {
                return in_array(mb_substr($lesson->due, 0, 10), $weekDates);
            });

            $lessonList = array_merge($lessonList, $data);
        }

        $byGrade = array();
        $byGroup = array();
        $byTeacherFio = array();

        foreach ($lessonList as $lesson) {
            $rightIndex = mb_strrpos($lesson->name, ')');
            $leftIndex = mb_strrpos($lesson->name, '(');
            $groupName = mb_substr($lesson->name, $leftIndex + 1, $rightIndex - $leftIndex - 1);
            if (strpos($groupName, ')') !== false) {
                $next_to_last = mb_strrpos($lesson->name, '(',  $leftIndex - mb_strlen($lesson->name) - 1);

                $lesson->groupName = mb_substr($lesson->name, $next_to_last+1, $leftIndex - $next_to_last - 2);
            } else {
                $lesson->groupName = $groupName;
            }

            $groups = StudentGroup::FacultyGroupsFromGroupName($lesson->groupName);

            if (count($groups) == 0) {
                continue;
            }

            if (count($groups) > 1) {
                $res = $client->get('cards/' . $lesson->id . '/list');
                $data = json_decode($res->getBody());
                $listName = $data->name;
                $leftIndex = mb_strpos($listName, '(');
                $lesson->groupName = mb_substr($listName, 0, $leftIndex - 1);
            } else {
                $lesson->groupName = $groups[0]->name;
            }
            $split = explode(' ', $lesson->groupName);
            $lesson->grade = $split[0];
            $lesson->letter = mb_substr($split[1], 0, 1);

            $nameSplit = explode(' - ', $lesson->name);
            $dateSplit = explode(' ', $nameSplit[0]);
            $lesson->date = $dateSplit[0];
            $lesson->dow = $dateSplit[1];
            $lesson->time = $dateSplit[2];
            $leftIndex = mb_strrpos($nameSplit[1], '(');
            $lesson->discName = mb_substr($nameSplit[1], 0, $leftIndex - 1);
            if (count($nameSplit) < 3) {
                dd($lesson);
            }
            $lesson->teacherFio = $nameSplit[2];

            //dd($lesson);

            if ((strpos(mb_strtolower($lesson->desc), 'онлайн') !== false) ||
                (strpos(mb_strtolower($lesson->desc), 'он лайн') !== false) ||
                (strpos(mb_strtolower($lesson->desc), 'он-лайн') !== false) ||
                (strpos(mb_strtolower($lesson->desc), 'zoom.us') !== false) ||
                (strpos(mb_strtolower($lesson->desc), 'online') !== false)) {
                if (!array_key_exists($lesson->grade, $byGrade)) {
                    $byGrade[$lesson->grade] = array('online' => 0, 'offline' => 0, 'empty' => 0, 'lessons' => array(),
                        'onlineLessons' => array(), 'offlineLessons' => array(), 'emptyLessons' => array());
                }
                $byGrade[$lesson->grade]['online']++;
                $byGrade[$lesson->grade]['lessons'][] = $lesson;
                $byGrade[$lesson->grade]['onlineLessons'][] = $lesson;

                if (!array_key_exists($lesson->groupName, $byGroup)) {
                    $byGroup[$lesson->groupName] = array('online' => 0, 'offline' => 0, 'empty' => 0, 'lessons' => array(),
                        'onlineLessons' => array(), 'offlineLessons' => array(), 'emptyLessons' => array());
                }
                $byGroup[$lesson->groupName]['online']++;
                $byGroup[$lesson->groupName]['lessons'][] = $lesson;
                $byGroup[$lesson->groupName]['onlineLessons'][] = $lesson;

                if (!array_key_exists($lesson->teacherFio, $byTeacherFio)) {
                    $byTeacherFio[$lesson->teacherFio] = array('online' => 0, 'offline' => 0, 'empty' => 0, 'byGroup' => array(), 'lessons' => array(),
                        'onlineLessons' => array(), 'offlineLessons' => array(), 'emptyLessons' => array());
                }
                $byTeacherFio[$lesson->teacherFio]['online']++;
                $byTeacherFio[$lesson->teacherFio]['lessons'][] = $lesson;
                $byTeacherFio[$lesson->teacherFio]['onlineLessons'][] = $lesson;

                if (!array_key_exists($lesson->groupName, $byTeacherFio[$lesson->teacherFio]['byGroup'])) {
                    $byTeacherFio[$lesson->teacherFio]['byGroup'][$lesson->groupName] = array('online' => 0, 'offline' => 0, 'empty' => 0,
                        'onlineLessons' => array(), 'offlineLessons' => array(), 'emptyLessons' => array());
                }
                $byTeacherFio[$lesson->teacherFio]['byGroup'][$lesson->groupName]['online']++;
                $byTeacherFio[$lesson->teacherFio]['byGroup'][$lesson->groupName]['lessons'][] = $lesson;
                $byTeacherFio[$lesson->teacherFio]['byGroup'][$lesson->groupName]['onlineLessons'][] = $lesson;
            } else {
                $offlineOrEmpty = 'offline';
                if ($lesson->desc === '') { $offlineOrEmpty = 'empty'; }

                if (!array_key_exists($lesson->grade, $byGrade)) {
                    $byGrade[$lesson->grade] = array('online' => 0, 'offline' => 0, 'empty' => 0, 'lessons' => array(),
                        'onlineLessons' => array(), 'offlineLessons' => array(), 'emptyLessons' => array());
                }
                $byGrade[$lesson->grade][$offlineOrEmpty]++;
                $byGrade[$lesson->grade]['lessons'][] = $lesson;
                $byGrade[$lesson->grade][$offlineOrEmpty . 'Lessons'][] = $lesson;

                if (!array_key_exists($lesson->groupName, $byGroup)) {
                    $byGroup[$lesson->groupName] = array('online' => 0, 'offline' => 0, 'empty' => 0, 'lessons' => array(),
                        'onlineLessons' => array(), 'offlineLessons' => array(), 'emptyLessons' => array());
                }
                $byGroup[$lesson->groupName][$offlineOrEmpty]++;
                $byGroup[$lesson->groupName]['lessons'][] = $lesson;
                $byGroup[$lesson->groupName][$offlineOrEmpty . 'Lessons'][] = $lesson;

                if (!array_key_exists($lesson->teacherFio, $byTeacherFio)) {
                    $byTeacherFio[$lesson->teacherFio] = array('online' => 0, 'offline' => 0, 'empty' => 0, 'byGroup' => array(), 'lessons' => array(),
                        'onlineLessons' => array(), 'offlineLessons' => array(), 'emptyLessons' => array());
                }
                $byTeacherFio[$lesson->teacherFio][$offlineOrEmpty]++;
                $byTeacherFio[$lesson->teacherFio]['lessons'][] = $lesson;
                $byTeacherFio[$lesson->teacherFio][$offlineOrEmpty . 'Lessons'][] = $lesson;

                if (!array_key_exists($lesson->groupName, $byTeacherFio[$lesson->teacherFio]['byGroup'])) {
                    $byTeacherFio[$lesson->teacherFio]['byGroup'][$lesson->groupName] = array('online' => 0, 'offline' => 0, 'empty' => 0,
                        'onlineLessons' => array(), 'offlineLessons' => array(), 'emptyLessons' => array());
                }
                $byTeacherFio[$lesson->teacherFio]['byGroup'][$lesson->groupName][$offlineOrEmpty]++;
                $byTeacherFio[$lesson->teacherFio]['byGroup'][$lesson->groupName]['lessons'][] = $lesson;
                $byTeacherFio[$lesson->teacherFio]['byGroup'][$lesson->groupName][$offlineOrEmpty . 'Lessons'][] = $lesson;
            }
        }

        foreach ($byTeacherFio as $key => $value) {
            $byTeacherFio[$key]['teacherFio'] = $key;
        }
        foreach ($byGroup as $key => $value) {
            $byGroup[$key]['groupName'] = $key;
        }

        foreach ($byGrade as $grade => $gradeItem) {
            usort($byGrade[$grade]["lessons"], function($a, $b) {
                if ($a->groupName === $b->groupName) {
                    try {
                        $aDT = $a->date . " " . $a->time;
                        $aCarbon = Carbon::createFromFormat("d.m H:i", $aDT);
                    } catch (\Exception $e) {
                        dd($a);
                    }

                    try {
                        $bDT = $b->date . " " . $b->time;
                        $bCarbon = Carbon::createFromFormat("d.m H:i", $bDT);
                    } catch (\Exception $e) {
                        dd($b);
                    }

                    if ($aCarbon->eq($bCarbon)) {
                        return 0;
                    } else {
                        return ($aCarbon->gt($bCarbon)) ? 1 : -1;
                    }
                } else {
                    return strcmp($a->groupName, $b->groupName);
                }
            });

            usort($byGrade[$grade]["onlineLessons"], function($a, $b) {
                if ($a->groupName === $b->groupName) {
                    $aDT = $a->date . " " . $a->time;
                    $aCarbon = Carbon::createFromFormat("d.m H:i", $aDT);
                    $bDT = $b->date . " " . $b->time;
                    $bCarbon = Carbon::createFromFormat("d.m H:i", $bDT);

                    if ($aCarbon->eq($bCarbon)) {
                        return 0;
                    } else {
                        return ($aCarbon->gt($bCarbon)) ? 1 : -1;
                    }
                } else {
                    return strcmp($a->groupName, $b->groupName);
                }
            });

            usort($byGrade[$grade]["offlineLessons"], function($a, $b) {
                if ($a->groupName === $b->groupName) {
                    $aDT = $a->date . " " . $a->time;
                    $aCarbon = Carbon::createFromFormat("d.m H:i", $aDT);
                    $bDT = $b->date . " " . $b->time;
                    $bCarbon = Carbon::createFromFormat("d.m H:i", $bDT);

                    if ($aCarbon->eq($bCarbon)) {
                        return 0;
                    } else {
                        return ($aCarbon->gt($bCarbon)) ? 1 : -1;
                    }
                } else {
                    return strcmp($a->groupName, $b->groupName);
                }
            });

            usort($byGrade[$grade]["emptyLessons"], function($a, $b) {
                if ($a->groupName === $b->groupName) {
                    $aDT = $a->date . " " . $a->time;
                    $aCarbon = Carbon::createFromFormat("d.m H:i", $aDT);
                    $bDT = $b->date . " " . $b->time;
                    $bCarbon = Carbon::createFromFormat("d.m H:i", $bDT);

                    if ($aCarbon->eq($bCarbon)) {
                        return 0;
                    } else {
                        return ($aCarbon->gt($bCarbon)) ? 1 : -1;
                    }
                } else {
                    return strcmp($a->groupName, $b->groupName);
                }
            });
        }

        foreach ($byGroup as $group => $groupItem) {
            usort($byGroup[$group]["lessons"], function($a, $b) {
                if ($a->groupName === $b->groupName) {
                    $aDT = $a->date . " " . $a->time;
                    $aCarbon = Carbon::createFromFormat("d.m H:i", $aDT);
                    $bDT = $b->date . " " . $b->time;
                    $bCarbon = Carbon::createFromFormat("d.m H:i", $bDT);

                    if ($aCarbon->eq($bCarbon)) {
                        return 0;
                    } else {
                        return ($aCarbon->gt($bCarbon)) ? 1 : -1;
                    }
                } else {
                    return strcmp($a->groupName, $b->groupName);
                }
            });

            usort($byGroup[$group]["onlineLessons"], function($a, $b) {
                if ($a->groupName === $b->groupName) {
                    $aDT = $a->date . " " . $a->time;
                    $aCarbon = Carbon::createFromFormat("d.m H:i", $aDT);
                    $bDT = $b->date . " " . $b->time;
                    $bCarbon = Carbon::createFromFormat("d.m H:i", $bDT);

                    if ($aCarbon->eq($bCarbon)) {
                        return 0;
                    } else {
                        return ($aCarbon->gt($bCarbon)) ? 1 : -1;
                    }
                } else {
                    return strcmp($a->groupName, $b->groupName);
                }
            });

            usort($byGroup[$group]["offlineLessons"], function($a, $b) {
                if ($a->groupName === $b->groupName) {
                    $aDT = $a->date . " " . $a->time;
                    $aCarbon = Carbon::createFromFormat("d.m H:i", $aDT);
                    $bDT = $b->date . " " . $b->time;
                    $bCarbon = Carbon::createFromFormat("d.m H:i", $bDT);

                    if ($aCarbon->eq($bCarbon)) {
                        return 0;
                    } else {
                        return ($aCarbon->gt($bCarbon)) ? 1 : -1;
                    }
                } else {
                    return strcmp($a->groupName, $b->groupName);
                }
            });

            usort($byGroup[$group]["emptyLessons"], function($a, $b) {
                if ($a->groupName === $b->groupName) {
                    $aDT = $a->date . " " . $a->time;
                    $aCarbon = Carbon::createFromFormat("d.m H:i", $aDT);
                    $bDT = $b->date . " " . $b->time;
                    $bCarbon = Carbon::createFromFormat("d.m H:i", $bDT);

                    if ($aCarbon->eq($bCarbon)) {
                        return 0;
                    } else {
                        return ($aCarbon->gt($bCarbon)) ? 1 : -1;
                    }
                } else {
                    return strcmp($a->groupName, $b->groupName);
                }
            });
        }

        foreach ($byTeacherFio as $teacher => $teacherItem) {
            usort($byTeacherFio[$teacher]["lessons"], function($a, $b) {
                $aDT = $a->date . " " . $a->time;
                $aCarbon = Carbon::createFromFormat("d.m H:i", $aDT);
                $bDT = $b->date . " " . $b->time;
                $bCarbon = Carbon::createFromFormat("d.m H:i", $bDT);

                if ($aCarbon->eq($bCarbon)) {
                    return 0;
                } else {
                    return ($aCarbon->gt($bCarbon)) ? 1 : -1;
                }
            });

            usort($byTeacherFio[$teacher]["onlineLessons"], function($a, $b) {
                $aDT = $a->date . " " . $a->time;
                $aCarbon = Carbon::createFromFormat("d.m H:i", $aDT);
                $bDT = $b->date . " " . $b->time;
                $bCarbon = Carbon::createFromFormat("d.m H:i", $bDT);

                if ($aCarbon->eq($bCarbon)) {
                    return 0;
                } else {
                    return ($aCarbon->gt($bCarbon)) ? 1 : -1;
                }
            });

            usort($byTeacherFio[$teacher]["offlineLessons"], function($a, $b) {
                $aDT = $a->date . " " . $a->time;
                $aCarbon = Carbon::createFromFormat("d.m H:i", $aDT);
                $bDT = $b->date . " " . $b->time;
                $bCarbon = Carbon::createFromFormat("d.m H:i", $bDT);

                if ($aCarbon->eq($bCarbon)) {
                    return 0;
                } else {
                    return ($aCarbon->gt($bCarbon)) ? 1 : -1;
                }
            });

            usort($byTeacherFio[$teacher]["emptyLessons"], function($a, $b) {
                $aDT = $a->date . " " . $a->time;
                $aCarbon = Carbon::createFromFormat("d.m H:i", $aDT);
                $bDT = $b->date . " " . $b->time;
                $bCarbon = Carbon::createFromFormat("d.m H:i", $bDT);

                if ($aCarbon->eq($bCarbon)) {
                    return 0;
                } else {
                    return ($aCarbon->gt($bCarbon)) ? 1 : -1;
                }
            });
        }

        $result['byGrade'] = $byGrade;
        $result['byGroup'] = array_values($byGroup);
        $result['byTeacherFio'] = array_values($byTeacherFio);

        return $result;
    }

    public static function GroupTrelloWeekCards($groupId, $week) {
        if (!array_key_exists($week, TrelloController::$trelloListIds)) {
            return array();
        }

        $group = StudentGroup::find($groupId);

        $listId = TrelloController::$trelloListIds[$week][$group->name];

        $stack = HandlerStack::create();
        $middleware = new Oauth1([
            'consumer_key'    => 'a8c89955d4d62ad9bd2f50c304d3dd9d',
            'consumer_secret' => 'bd7e2095b3013b1a70f435f5ff936c6ded7503d9bfec351b89f480eddc6702cf',
            'token'           => 'f41efa8a36f62ce93bcd4b0aee9777ccc0e4dac326840cd6c2caf9df3f586153',
            'token_secret'    => 'bd7e2095b3013b1a70f435f5ff936c6ded7503d9bfec351b89f480eddc6702cf'
        ]);
        $stack->push($middleware);
        $client = new Client([
            'base_uri' => 'https://api.trello.com/1/',
            'handler' => $stack,
            'auth' => 'oauth'
        ]);

        $res = $client->get('lists/' . $listId .'/cards');
        $data = json_decode($res->getBody());

        return $data;

    }

    public static function GroupTrelloDateCards($groupId, $date) {
        $calendar = DB::table('calendars')
            ->where('date', '=', $date)
            ->first();
        if ($calendar == null) return array();

        $ss = ConfigOption::SemesterStarts();

        $css = Carbon::createFromFormat('Y-m-d', $ss);

        $week = Calendar::WeekFromDate($calendar->date, $css);

        if (!array_key_exists($week, TrelloController::$trelloListIds)) {
            return array();
        }

        $group = StudentGroup::find($groupId);

        $listId = TrelloController::$trelloListIds[$week][$group->name];

        $stack = HandlerStack::create();
        $middleware = new Oauth1([
            'consumer_key'    => 'a8c89955d4d62ad9bd2f50c304d3dd9d',
            'consumer_secret' => 'bd7e2095b3013b1a70f435f5ff936c6ded7503d9bfec351b89f480eddc6702cf',
            'token'           => 'f41efa8a36f62ce93bcd4b0aee9777ccc0e4dac326840cd6c2caf9df3f586153',
            'token_secret'    => 'bd7e2095b3013b1a70f435f5ff936c6ded7503d9bfec351b89f480eddc6702cf'
        ]);
        $stack->push($middleware);
        $client = new Client([
            'base_uri' => 'https://api.trello.com/1/',
            'handler' => $stack,
            'auth' => 'oauth'
        ]);

        $res = $client->get('lists/' . $listId .'/cards');
        $data = json_decode($res->getBody());

        $data = array_filter($data, function($card) use ($date) {
            return mb_substr($card->due, 0 , 10) === $date;
        });

        return $data;
    }
}
