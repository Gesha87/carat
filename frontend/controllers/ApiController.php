<?php
namespace frontend\controllers;

use Yii;
use yii\mongodb\Collection;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\Response;
use yii\web\UploadedFile;

/**
 * Api controller
 */
class ApiController extends Controller
{
	public $modelNameForModelIdentifier = [
		'iPhone1,1' => 'iPhone 1G',
		'iPhone1,2' => 'iPhone 3G',
		'iPhone2,1' => 'iPhone 3GS',
		'iPhone3,1' => 'iPhone 4 (GSM)',
		'iPhone3,2' => 'iPhone 4 (GSM Rev A)',
		'iPhone3,3' => 'iPhone 4 (CDMA)',
		'iPhone4,1' => 'iPhone 4S',
		'iPhone5,1' => 'iPhone 5 (GSM)',
		'iPhone5,2' => 'iPhone 5 (Global)',
		'iPhone5,3' => 'iPhone 5c (GSM)',
		'iPhone5,4' => 'iPhone 5c (Global)',
		'iPhone6,1' => 'iPhone 5s (GSM)',
		'iPhone6,2' => 'iPhone 5s (Global)',
		'iPhone7,1' => 'iPhone 6 Plus',
		'iPhone7,2' => 'iPhone 6',

		'iPad1,1' => 'iPad 1G',
		'iPad2,1' => 'iPad 2 (Wi-Fi)',
		'iPad2,2' => 'iPad 2 (GSM)',
		'iPad2,3' => 'iPad 2 (CDMA)',
		'iPad2,4' => 'iPad 2 (Rev A)',
		'iPad3,1' => 'iPad 3 (Wi-Fi)',
		'iPad3,2' => 'iPad 3 (GSM)',
		'iPad3,3' => 'iPad 3 (Global)',
		'iPad3,4' => 'iPad 4 (Wi-Fi)',
		'iPad3,5' => 'iPad 4 (GSM)',
		'iPad3,6' => 'iPad 4 (Global)',

		'iPad4,1' => 'iPad Air (Wi-Fi)',
		'iPad4,2' => 'iPad Air (Cellular)',
		'iPad5,3' => 'iPad Air 2 (Wi-Fi)',
		'iPad5,4' => 'iPad Air 2 (Cellular)',

		'iPad2,5' => 'iPad mini 1G (Wi-Fi)',
		'iPad2,6' => 'iPad mini 1G (GSM)',
		'iPad2,7' => 'iPad mini 1G (Global)',
		'iPad4,4' => 'iPad mini 2G (Wi-Fi)',
		'iPad4,5' => 'iPad mini 2G (Cellular)',
		'iPad4,7' => 'iPad mini 3G (Wi-Fi)',
		'iPad4,8' => 'iPad mini 3G (Cellular)',
		'iPad4,9' => 'iPad mini 3G (Cellular)',

		'iPod1,1' => 'iPod touch 1G',
		'iPod2,1' => 'iPod touch 2G',
		'iPod3,1' => 'iPod touch 3G',
		'iPod4,1' => 'iPod touch 4G',
		'iPod5,1' => 'iPod touch 5G',
	];

	public function init()
	{
		parent::init();
		Yii::$app->response->data = [
			'data' => null,
			'error' => [
				'code' => 0,
				'message' => '',
			],
		];
	}

    public function actionSend()
    {
		$acraParams = Yii::$app->request->post();
		if (isset($acraParams['PACKAGE_NAME'], $acraParams['STACK_TRACE'], $acraParams['APP_VERSION_NAME'], $acraParams['APP_VERSION_CODE'], $acraParams['USER_CRASH_DATE'])) {
			$fullInfo = json_encode($acraParams);
			$packageName = $acraParams['PACKAGE_NAME'];
			$stackTrace = $acraParams['STACK_TRACE'];
			$stack = explode("\n", $stackTrace);
			$stackTraceMini = preg_replace('/:.+/', '', $stack[0], 1);
			$correctable = false;
			foreach ($stack as $line) {
				if (strpos($line, 'at '.$packageName) !== false) {
					$stackTraceMini .= "\n...".$line;
					$correctable = true;
					break;
				}
			}
			$hashMini = md5($stackTraceMini);
			$hash = md5($stackTrace);
			$appVersionName = $acraParams['APP_VERSION_NAME'];
			$appVersionCode = (int)$acraParams['APP_VERSION_CODE'];
			$userCrashDate = new \MongoDate(strtotime($acraParams['USER_CRASH_DATE']));
			$document = [
				'package_name' => $packageName,
				'hash' => $hash,
				'hash_mini' => $hashMini,
				'stack_trace' => iconv('UTF-8', 'UTF-8//IGNORE', $stackTrace),
				'stack_trace_mini' => iconv('UTF-8', 'UTF-8//IGNORE', $stackTraceMini),
				'app_version_name' => $appVersionName,
				'app_version_code' => $appVersionCode,
				'user_crash_date' => $userCrashDate,
				'full_info' => iconv('UTF-8', 'UTF-8//IGNORE', $fullInfo),
				'resolved' => 0,
			];
			$customData = (string)@$acraParams['CUSTOM_DATA'];
			if (strpos($customData, 'logType = info') !== false) {
				$document['info'] = 1;
			}
			if ($correctable) {
				$document['correctable'] = 1;
			}
			/* @var $collection Collection */
			$collection = Yii::$app->mongodb->getCollection('crash');
			$collection->insert($document);
			Yii::$app->response->data['data'] = [
				'status' => true
			];
		} else {
			Yii::$app->response->data['error'] = [
				'code' => 1,
				'message' => 'Wrong params',
			];
		}
    }

	public function actionSendApple()
	{
		$xmlstring = Yii::$app->request->post('xmlstring');
		$xmlstring = '<crashes><crash><applicationname>BoostTest</applicationname><uuids><uuid type="app" arch="armv7">cd6d8e3e798a33068360182e4e2109bc</uuid></uuids><bundleidentifier>AndrewRomanov.BoostTest</bundleidentifier><systemversion>8.0</systemversion><platform>iPhone4,1</platform><senderversion>1</senderversion><version>1</version><uuid>F20AD3C0-B208-4A9C-8B2E-A91FD387B59E</uuid><log><![CDATA[Incident Identifier: 8488F655-C0BE-42DC-BC2C-5A841AED4E3E
CrashReporter Key:   998af9f0e405a82c4b3ff911db641745d36120aa
Hardware Model:      iPhone4,1
Process:             BoostTest [7864]
Path:                /private/var/mobile/Containers/Bundle/Application/766E2136-36B7-4706-8054-1822F866B792/BoostTest.app/BoostTest
Identifier:          AndrewRomanov.BoostTest
Version:             1 (1.0)
Code Type:           ARM (Native)
Parent Process:      launchd [1]

Date/Time:           2015-01-28 13:05:37.009 +0600
Launch Time:         2015-01-28 13:05:36.686 +0600
OS Version:          iOS 8.0 (12A365)
Report Version:      105

Exception Type:  EXC_CRASH (SIGABRT)
Exception Codes: 0x0000000000000000, 0x0000000000000000
Triggered by Thread:  0

Last Exception Backtrace:
0   CoreFoundation                	0x2bc10f82 __exceptionPreprocess + 122
1   libobjc.A.dylib               	0x3936fc72 objc_exception_throw + 34
2   CoreFoundation                	0x2bb25632 -[__NSArrayI objectAtIndex:] + 174
3   BoostTest                     	0x00052e86 -[ViewController viewDidLoad] (ViewController.m:20)
4   UIKit                         	0x2f08ed38 -[UIViewController loadViewIfRequired] + 596
5   UIKit                         	0x2f08eaa8 -[UIViewController view] + 20
6   UIKit                         	0x2f09498a -[UIWindow addRootViewControllerViewIfPossible] + 58
7   UIKit                         	0x2f0923f8 -[UIWindow _setHidden:forced:] + 300
8   UIKit                         	0x2f0fc4e4 -[UIWindow makeKeyAndVisible] + 44
9   UIKit                         	0x2f2ee29c -[UIApplication _callInitializationDelegatesForMainScene:transitionContext:] + 2572
10  UIKit                         	0x2f2f06f4 -[UIApplication _runWithMainScene:transitionContext:completion:] + 1364
11  UIKit                         	0x2f2fafa4 __84-[UIApplication _handleApplicationActivationWithScene:transitionContext:completion:]_block_invoke + 32
12  UIKit                         	0x2f2eef96 -[UIApplication workspaceDidEndTransaction:] + 126
13  FrontBoardServices            	0x323320dc __31-[FBSSerialQueue performAsync:]_block_invoke + 8
14  CoreFoundation                	0x2bbd7828 __CFRUNLOOP_IS_CALLING_OUT_TO_A_BLOCK__ + 8
15  CoreFoundation                	0x2bbd6aec __CFRunLoopDoBlocks + 212
16  CoreFoundation                	0x2bbd5646 __CFRunLoopRun + 1710
17  CoreFoundation                	0x2bb22dac CFRunLoopRunSpecific + 472
18  CoreFoundation                	0x2bb22bbe CFRunLoopRunInMode + 102
19  UIKit                         	0x2f0f3102 -[UIApplication _run] + 554
20  UIKit                         	0x2f0edefc UIApplicationMain + 1436
21  BoostTest                     	0x00052f6a main (main.m:14)
22  libdyld.dylib                 	0x3990baaa tlv_initializer + 2


Thread 0 name:  Dispatch queue: com.apple.main-thread
Thread 0 Crashed:
0   libsystem_kernel.dylib        	0x399d0dfc __pthread_kill + 8
1   libsystem_pthread.dylib       	0x39a50d0e pthread_kill + 58
2   libsystem_c.dylib             	0x39970934 abort + 72
3   libc++abi.dylib               	0x38b84bb8 abort_message + 84
4   libc++abi.dylib               	0x38b9e66a default_terminate_handler() + 262
5   libobjc.A.dylib               	0x3936ff0e _objc_terminate() + 190
6   libc++abi.dylib               	0x38b9bdec std::__terminate(void (*)()) + 76
7   libc++abi.dylib               	0x38b9b8b4 __cxa_rethrow + 96
8   libobjc.A.dylib               	0x3936fdba objc_exception_rethrow + 38
9   CoreFoundation                	0x2bb22e48 CFRunLoopRunSpecific + 628
10  CoreFoundation                	0x2bb22bbe CFRunLoopRunInMode + 102
11  UIKit                         	0x2f0f3102 -[UIApplication _run] + 554
12  UIKit                         	0x2f0edefc UIApplicationMain + 1436
13  BoostTest                     	0x00052f6a main (main.m:14)
14  libdyld.dylib                 	0x3990baac start + 0

Thread 1 name:  Dispatch queue: com.apple.libdispatch-manager
Thread 1:
0   libsystem_kernel.dylib        	0x399bd2c8 kevent64 + 24
1   libdispatch.dylib             	0x398dfec4 _dispatch_mgr_invoke + 276
2   libdispatch.dylib             	0x398dfbf6 _dispatch_mgr_thread$VARIANT$mp + 34

Thread 2:
0   libsystem_kernel.dylib        	0x399d19cc __workq_kernreturn + 8
1   libsystem_pthread.dylib       	0x39a4de9c _pthread_wqthread + 788
2   libsystem_pthread.dylib       	0x39a4db74 start_wqthread + 4

Thread 3:
0   libsystem_kernel.dylib        	0x399d19cc __workq_kernreturn + 8
1   libsystem_pthread.dylib       	0x39a4de9c _pthread_wqthread + 788
2   libsystem_pthread.dylib       	0x39a4db74 start_wqthread + 4

Thread 4:
0   libsystem_kernel.dylib        	0x399d19cc __workq_kernreturn + 8
1   libsystem_pthread.dylib       	0x39a4de9c _pthread_wqthread + 788
2   libsystem_pthread.dylib       	0x39a4db74 start_wqthread + 4

Thread 5:
0   libsystem_kernel.dylib        	0x399d19cc __workq_kernreturn + 8
1   libsystem_pthread.dylib       	0x39a4de9c _pthread_wqthread + 788
2   libsystem_pthread.dylib       	0x39a4db74 start_wqthread + 4

Thread 0 crashed with ARM Thread State (32-bit):
    r0: 0x00000000    r1: 0x00000000      r2: 0x00000000      r3: 0x00000003
    r4: 0x00000006    r5: 0x3c0ce9dc      r6: 0x3c0ba840      r7: 0x0015b418
    r8: 0x14541df0    r9: 0x7420666f     r10: 0x3c0b9074     r11: 0x14541e14
    ip: 0x00000148    sp: 0x0015b40c      lr: 0x39a50d13      pc: 0x399d0dfc
  cpsr: 0x00000010

Binary Images:
0x4c000 - 0x53fff BoostTest armv7  <613051fda9c63b23a3c954fbe95aeeec> /var/mobile/Containers/Bundle/Application/766E2136-36B7-4706-8054-1822F866B792/BoostTest.app/BoostTest
0x1fe5c000 - 0x1fe7ffff dyld armv7  <95260bf90542373e9c428676a19740fa> /usr/lib/dyld
0x2a6c6000 - 0x2a832fff AVFoundation armv7  <82d1ce4181dc3bf4bd730f7492f05a92> /System/Library/Frameworks/AVFoundation.framework/AVFoundation
0x2a833000 - 0x2a891fff libAVFAudio.dylib armv7  <1b7ba3faa65c3850a72317a96ec43025> /System/Library/Frameworks/AVFoundation.framework/libAVFAudio.dylib
0x2a8cc000 - 0x2a8ccfff Accelerate armv7  <6489308d54873b748557138e7540d733> /System/Library/Frameworks/Accelerate.framework/Accelerate
0x2a8dd000 - 0x2aaf6fff vImage armv7  <9df0c9ce894a39f381ee083b84f63faa> /System/Library/Frameworks/Accelerate.framework/Frameworks/vImage.framework/vImage
0x2aaf7000 - 0x2abd4fff libBLAS.dylib armv7  <f7c32b998307330ba5f35ecac6b06824> /System/Library/Frameworks/Accelerate.framework/Frameworks/vecLib.framework/libBLAS.dylib
0x2abd5000 - 0x2ae98fff libLAPACK.dylib armv7  <7964d0bd893432319fca6e8cddb2b119> /System/Library/Frameworks/Accelerate.framework/Frameworks/vecLib.framework/libLAPACK.dylib
0x2ae99000 - 0x2aeabfff libLinearAlgebra.dylib armv7  <c3f84b77ae383b5594fe0390218ecb8d> /System/Library/Frameworks/Accelerate.framework/Frameworks/vecLib.framework/libLinearAlgebra.dylib
0x2aeac000 - 0x2af21fff libvDSP.dylib armv7  <2e809e9146b536958f515517ed636a5e> /System/Library/Frameworks/Accelerate.framework/Frameworks/vecLib.framework/libvDSP.dylib
0x2af22000 - 0x2af33fff libvMisc.dylib armv7  <036ca731b2cf35cbb2762d08f6a16dfe> /System/Library/Frameworks/Accelerate.framework/Frameworks/vecLib.framework/libvMisc.dylib
0x2af34000 - 0x2af34fff vecLib armv7  <332674aaa39b3d94993ff677468b9d87> /System/Library/Frameworks/Accelerate.framework/Frameworks/vecLib.framework/vecLib
0x2af35000 - 0x2af5bfff Accounts armv7  <13071672ad5c35fe9ebad729d8cbf029> /System/Library/Frameworks/Accounts.framework/Accounts
0x2af5d000 - 0x2afcdfff AddressBook armv7  <3ca79f630bd93678ae5a115995754f5c> /System/Library/Frameworks/AddressBook.framework/AddressBook
0x2b288000 - 0x2b4f9fff AudioToolbox armv7  <503cd792e29c3a22a0e6797a3a1f052a> /System/Library/Frameworks/AudioToolbox.framework/AudioToolbox
0x2b65f000 - 0x2b7e6fff CFNetwork armv7  <fa99828f278f3d6fae61a96388b3b52e> /System/Library/Frameworks/CFNetwork.framework/CFNetwork
0x2b868000 - 0x2b8c6fff CoreAudio armv7  <711dada83c8c3c29aaf9d17c1e088a94> /System/Library/Frameworks/CoreAudio.framework/CoreAudio
0x2b8df000 - 0x2b8fcfff CoreBluetooth armv7  <771793a76e38327390b62d1191240128> /System/Library/Frameworks/CoreBluetooth.framework/CoreBluetooth
0x2b8fd000 - 0x2bb08fff CoreData armv7  <92438c5f749d35a18446ba947d7767db> /System/Library/Frameworks/CoreData.framework/CoreData
0x2bb09000 - 0x2be38fff CoreFoundation armv7  <50123e7208983ab8a3f8801b22df38f2> /System/Library/Frameworks/CoreFoundation.framework/CoreFoundation
0x2be39000 - 0x2bf62fff CoreGraphics armv7  <66e9fc039182333c9ac1900bc3e787a8> /System/Library/Frameworks/CoreGraphics.framework/CoreGraphics
0x2c1b3000 - 0x2c2c0fff CoreImage armv7  <52f3d145099233a1ae5bf151a30968c9> /System/Library/Frameworks/CoreImage.framework/CoreImage
0x2c34a000 - 0x2c3e4fff CoreMedia armv7  <cfaa8fe16921322a8d051be58f0ba5a8> /System/Library/Frameworks/CoreMedia.framework/CoreMedia
0x2c3e5000 - 0x2c4a5fff CoreMotion armv7  <8f994a9a503c3cdf9f633b33905765c8> /System/Library/Frameworks/CoreMotion.framework/CoreMotion
0x2c4a6000 - 0x2c504fff CoreTelephony armv7  <1865940137713dcfae217d4b0de6958d> /System/Library/Frameworks/CoreTelephony.framework/CoreTelephony
0x2c505000 - 0x2c5ccfff CoreText armv7  <44d68be1c4173eefb39dee7fb833ab5f> /System/Library/Frameworks/CoreText.framework/CoreText
0x2c5cd000 - 0x2c5e2fff CoreVideo armv7  <e73497bc01ad3dfa9021bbd545b238ec> /System/Library/Frameworks/CoreVideo.framework/CoreVideo
0x2c84e000 - 0x2ca50fff Foundation armv7  <b2799048242f307c9892b904a9b36a09> /System/Library/Frameworks/Foundation.framework/Foundation
0x2cb30000 - 0x2cb86fff IOKit armv7  <cf83377abd033964b8ea67aa40a4f8fc> /System/Library/Frameworks/IOKit.framework/Versions/A/IOKit
0x2cb87000 - 0x2cdc9fff ImageIO armv7  <4d8d2eec2be7389f9bef4cc206ab1c51> /System/Library/Frameworks/ImageIO.framework/ImageIO
0x2cdca000 - 0x2d113fff JavaScriptCore armv7  <c60465cbbdac337ba85277e4ac295dbc> /System/Library/Frameworks/JavaScriptCore.framework/JavaScriptCore
0x2d3e2000 - 0x2d3eafff MediaAccessibility armv7  <c46f7282336e3a3598119ed19753bcfe> /System/Library/Frameworks/MediaAccessibility.framework/MediaAccessibility
0x2d5c5000 - 0x2d93dfff MediaToolbox armv7  <6b17033e979c3cbdbe59068d214c3c8c> /System/Library/Frameworks/MediaToolbox.framework/MediaToolbox
0x2d9fd000 - 0x2da69fff Metal armv7  <d971a1b8b8d637a98f1c9131c6bc75c6> /System/Library/Frameworks/Metal.framework/Metal
0x2da6a000 - 0x2daf9fff MobileCoreServices armv7  <6350e1d9a8233b0b8663716cbc0255be> /System/Library/Frameworks/MobileCoreServices.framework/MobileCoreServices
0x2e5c3000 - 0x2e5cbfff OpenGLES armv7  <c87c554261393aa5bf85fac5c48ba03a> /System/Library/Frameworks/OpenGLES.framework/OpenGLES
0x2e5cd000 - 0x2e5cdfff libCVMSPluginSupport.dylib armv7  <e78a40f2f13c33dd8d80c8972f0da994> /System/Library/Frameworks/OpenGLES.framework/libCVMSPluginSupport.dylib
0x2e5ce000 - 0x2e5d0fff libCoreFSCache.dylib armv7  <464a9b4119763133a2d9351025007b45> /System/Library/Frameworks/OpenGLES.framework/libCoreFSCache.dylib
0x2e5d1000 - 0x2e5d4fff libCoreVMClient.dylib armv7  <d9e168b21cf63cb9b4cd49d30bfad66c> /System/Library/Frameworks/OpenGLES.framework/libCoreVMClient.dylib
0x2e5d5000 - 0x2e5ddfff libGFXShared.dylib armv7  <431e1f47629237109f9ab0ba085deab3> /System/Library/Frameworks/OpenGLES.framework/libGFXShared.dylib
0x2e5de000 - 0x2e61dfff libGLImage.dylib armv7  <0009f6d507073ee7891d95f6e2b12729> /System/Library/Frameworks/OpenGLES.framework/libGLImage.dylib
0x2eaab000 - 0x2ebfbfff QuartzCore armv7  <6245b916ab9d326e9de1b06c3ef06d8d> /System/Library/Frameworks/QuartzCore.framework/QuartzCore
0x2ee3d000 - 0x2ee7dfff Security armv7  <2245b2e838543824aeb75488acf4ff10> /System/Library/Frameworks/Security.framework/Security
0x2f021000 - 0x2f07dfff SystemConfiguration armv7  <184b320bb96d38bd90198ab7d330874e> /System/Library/Frameworks/SystemConfiguration.framework/SystemConfiguration
0x2f080000 - 0x2f918fff UIKit armv7  <ee8cc4162cf6359b9f06840d1dfd047d> /System/Library/Frameworks/UIKit.framework/UIKit
0x2f919000 - 0x2f980fff VideoToolbox armv7  <d85f06b8d5b03255b1ed26b392f10356> /System/Library/Frameworks/VideoToolbox.framework/VideoToolbox
0x30052000 - 0x30056fff AggregateDictionary armv7  <b3a8631ab71b38dd89b8a5c12523e88b> /System/Library/PrivateFrameworks/AggregateDictionary.framework/AggregateDictionary
0x3021b000 - 0x30245fff AirPlaySupport armv7  <701f2b977a83350dbcc270660cdeb9ab> /System/Library/PrivateFrameworks/AirPlaySupport.framework/AirPlaySupport
0x3043d000 - 0x3047bfff AppSupport armv7  <ef00e8bfb45f30eba14f2814991daecb> /System/Library/PrivateFrameworks/AppSupport.framework/AppSupport
0x305aa000 - 0x305e7fff AppleJPEG armv7  <bece852b679738698169770603dbe38a> /System/Library/PrivateFrameworks/AppleJPEG.framework/AppleJPEG
0x30605000 - 0x3060bfff AppleSRP armv7  <b6847dfcc40334ea9722d3f13554a46d> /System/Library/PrivateFrameworks/AppleSRP.framework/AppleSRP
0x30640000 - 0x30649fff AssertionServices armv7  <c977f6aef66130c39617094d607ee5c6> /System/Library/PrivateFrameworks/AssertionServices.framework/AssertionServices
0x3064a000 - 0x30662fff AssetsLibraryServices armv7  <be8b0ab42954333181d42f0e6c49aa59> /System/Library/PrivateFrameworks/AssetsLibraryServices.framework/AssetsLibraryServices
0x306b6000 - 0x306bafff BTLEAudioController armv7  <d93ccb2b76d83d4397f2d52d0453e553> /System/Library/PrivateFrameworks/BTLEAudioController.framework/BTLEAudioController
0x306bb000 - 0x306d2fff BackBoardServices armv7  <90f2f2097cd93fd29b3215c2cd4f19b5> /System/Library/PrivateFrameworks/BackBoardServices.framework/BackBoardServices
0x306d5000 - 0x3070afff BaseBoard armv7  <91b34e60fd50334e9fc2b4567236cc65> /System/Library/PrivateFrameworks/BaseBoard.framework/BaseBoard
0x3070b000 - 0x30711fff BluetoothManager armv7  <1d489c9a1222307c81ec3a712c08bdda> /System/Library/PrivateFrameworks/BluetoothManager.framework/BluetoothManager
0x30939000 - 0x30941fff CaptiveNetwork armv7  <588eadc07ec13c5e8d309447f31a183e> /System/Library/PrivateFrameworks/CaptiveNetwork.framework/CaptiveNetwork
0x30942000 - 0x30a64fff Celestial armv7  <3d9133a892cb3fdaa79a1e5dafca8821> /System/Library/PrivateFrameworks/Celestial.framework/Celestial
0x3107b000 - 0x3108bfff CommonUtilities armv7  <e94371f0c3af3df2b36c35c11e91e77a> /System/Library/PrivateFrameworks/CommonUtilities.framework/CommonUtilities
0x3108c000 - 0x31090fff CommunicationsFilter armv7  <797d402b98a9374fb920676f8c03cd18> /System/Library/PrivateFrameworks/CommunicationsFilter.framework/CommunicationsFilter
0x3113d000 - 0x31140fff CoreAUC armv7  <73fc866359063f30baf942f7e3f61f60> /System/Library/PrivateFrameworks/CoreAUC.framework/CoreAUC
0x311b9000 - 0x311d2fff CoreDuet armv7  <8884c503754d3e11bef8665285845f4c> /System/Library/PrivateFrameworks/CoreDuet.framework/CoreDuet
0x311d7000 - 0x311e6fff CoreDuetDaemonProtocol armv7  <b145140cf14733c78ab45771ac23a25a> /System/Library/PrivateFrameworks/CoreDuetDaemonProtocol.framework/CoreDuetDaemonProtocol
0x311ed000 - 0x311effff CoreDuetDebugLogging armv7  <5360d9f729b33046879514799ee116d6> /System/Library/PrivateFrameworks/CoreDuetDebugLogging.framework/CoreDuetDebugLogging
0x317d7000 - 0x31857fff CoreUI armv7  <1c2e75c0a2973beb827d13d08802b2f1> /System/Library/PrivateFrameworks/CoreUI.framework/CoreUI
0x31858000 - 0x318c1fff CoreUtils armv7  <9e41c4ebf773341a806198247518793d> /System/Library/PrivateFrameworks/CoreUtils.framework/CoreUtils
0x318c2000 - 0x318c7fff CrashReporterSupport armv7  <b390f4fa5603386ea83d56f48fdeacd8> /System/Library/PrivateFrameworks/CrashReporterSupport.framework/CrashReporterSupport
0x31b14000 - 0x31b35fff DataAccessExpress armv7  <68681cbe3ed937f6a74291ed288e2395> /System/Library/PrivateFrameworks/DataAccessExpress.framework/DataAccessExpress
0x31b74000 - 0x31b7afff DataMigration armv7  <50bff91cdbdf3a6ebe55c4cdfd1318b5> /System/Library/PrivateFrameworks/DataMigration.framework/DataMigration
0x31b84000 - 0x31b85fff DiagnosticLogCollection armv7  <4d0d31ac66e43d03a4fba8eec2bebffe> /System/Library/PrivateFrameworks/DiagnosticLogCollection.framework/DiagnosticLogCollection
0x31b86000 - 0x31ba0fff DictionaryServices armv7  <606f886854653188a384feb88c727c6b> /System/Library/PrivateFrameworks/DictionaryServices.framework/DictionaryServices
0x31bbf000 - 0x31bdefff EAP8021X armv7  <1b02879f4e23365a83c8e9bb7e480729> /System/Library/PrivateFrameworks/EAP8021X.framework/EAP8021X
0x31d11000 - 0x32134fff FaceCore armv7  <96fe282a3c903468bccffee0f45ffa2a> /System/Library/PrivateFrameworks/FaceCore.framework/FaceCore
0x32159000 - 0x32159fff FontServices armv7  <00fa3270936839b4bb7c64f2be3e7f50> /System/Library/PrivateFrameworks/FontServices.framework/FontServices
0x3215a000 - 0x3222dfff libFontParser.dylib armv7  <a9532d1a53a138de80e57f38b4d585a1> /System/Library/PrivateFrameworks/FontServices.framework/libFontParser.dylib
0x3231d000 - 0x32338fff FrontBoardServices armv7  <bc58f1f043413960b218aeb7f8b50286> /System/Library/PrivateFrameworks/FrontBoardServices.framework/FrontBoardServices
0x32ea4000 - 0x32eb4fff GraphicsServices armv7  <9efdf5cfa1b8391bbbf872c4496f5825> /System/Library/PrivateFrameworks/GraphicsServices.framework/GraphicsServices
0x33116000 - 0x3316cfff IDS armv7  <13c160f9f3ba38d6bbf0f6b3811ba1e4> /System/Library/PrivateFrameworks/IDS.framework/IDS
0x3316d000 - 0x3318efff IDSFoundation armv7  <709c320879a235b6886a0226796ec4ea> /System/Library/PrivateFrameworks/IDSFoundation.framework/IDSFoundation
0x3333c000 - 0x333a0fff IMFoundation armv7  <3dace4bdefff3757b5e669b0c68b3518> /System/Library/PrivateFrameworks/IMFoundation.framework/IMFoundation
0x333a8000 - 0x333abfff IOAccelerator armv7  <5b718623c85d3670b62447ace8d4b00d> /System/Library/PrivateFrameworks/IOAccelerator.framework/IOAccelerator
0x333ae000 - 0x333b4fff IOMobileFramebuffer armv7  <1f7e71ccb940316abc4d3c702d6d8830> /System/Library/PrivateFrameworks/IOMobileFramebuffer.framework/IOMobileFramebuffer
0x333b5000 - 0x333bafff IOSurface armv7  <30ff04d22c7632028ca42a4e0c97b2ae> /System/Library/PrivateFrameworks/IOSurface.framework/IOSurface
0x333bb000 - 0x333bcfff IOSurfaceAccelerator armv7  <ca94112c1c7e3d5f8f386e3d86fc22ca> /System/Library/PrivateFrameworks/IOSurfaceAccelerator.framework/IOSurfaceAccelerator
0x3345f000 - 0x33495fff LanguageModeling armv7  <b40438d2f27b389cb73743c61d3c011c> /System/Library/PrivateFrameworks/LanguageModeling.framework/LanguageModeling
0x335f1000 - 0x33690fff ManagedConfiguration armv7  <3d453a4bdcd735caa12ec7bac4a430c8> /System/Library/PrivateFrameworks/ManagedConfiguration.framework/ManagedConfiguration
0x3369a000 - 0x3369bfff Marco armv7  <68cbf0bb03a23415af7f9a724f9f7d5c> /System/Library/PrivateFrameworks/Marco.framework/Marco
0x3369c000 - 0x33713fff MediaControlSender armv7  <91c297b9dc3131d0afb22e56b628a230> /System/Library/PrivateFrameworks/MediaControlSender.framework/MediaControlSender
0x337b2000 - 0x337c4fff MediaRemote armv7  <eb5a6085e4433915be19daaf8f31c7e2> /System/Library/PrivateFrameworks/MediaRemote.framework/MediaRemote
0x337c5000 - 0x337d4fff MediaServices armv7  <1d6bd929ff1c3e92892315cadafba2d8> /System/Library/PrivateFrameworks/MediaServices.framework/MediaServices
0x33949000 - 0x33956fff MobileAsset armv7  <876d4a6cda803783a7588999c2247225> /System/Library/PrivateFrameworks/MobileAsset.framework/MobileAsset
0x3397d000 - 0x33986fff MobileBluetooth armv7  <a323e89fcb3d35beab1f7dba97b3f0a1> /System/Library/PrivateFrameworks/MobileBluetooth.framework/MobileBluetooth
0x339aa000 - 0x339b1fff MobileInstallation armv7  <4af983ee77833064bc7503cbed22e164> /System/Library/PrivateFrameworks/MobileInstallation.framework/MobileInstallation
0x339b2000 - 0x339befff MobileKeyBag armv7  <26e4ea84cc393fbdbec77b0024e144e7> /System/Library/PrivateFrameworks/MobileKeyBag.framework/MobileKeyBag
0x339eb000 - 0x339eefff MobileSystemServices armv7  <ae4ef39d4c073c5395b7070df717326d> /System/Library/PrivateFrameworks/MobileSystemServices.framework/MobileSystemServices
0x33a10000 - 0x33a1dfff MobileWiFi armv7  <156a2f7f3abb3dd79df11aeb6b9dab66> /System/Library/PrivateFrameworks/MobileWiFi.framework/MobileWiFi
0x33c93000 - 0x33c98fff Netrb armv7  <74cd9bc39770377d982a67cff76cccc7> /System/Library/PrivateFrameworks/Netrb.framework/Netrb
0x33c99000 - 0x33c9ffff NetworkStatistics armv7  <9d8ed51a234836048ce9f8d711a60f72> /System/Library/PrivateFrameworks/NetworkStatistics.framework/NetworkStatistics
0x33cc3000 - 0x33cc5fff OAuth armv7  <1b4ad044062c30d29f87f399bd847c25> /System/Library/PrivateFrameworks/OAuth.framework/OAuth
0x3441d000 - 0x34459fff OpenCL armv7  <8c9c716a2de93b38be82b2277bb9632d> /System/Library/PrivateFrameworks/OpenCL.framework/OpenCL
0x3453f000 - 0x34566fff PersistentConnection armv7  <f0c9f9b0930c3925948e5b528515d1ac> /System/Library/PrivateFrameworks/PersistentConnection.framework/PersistentConnection
0x3493d000 - 0x34987fff PhysicsKit armv7  <3d40d332c97c32ac80772192145ea414> /System/Library/PrivateFrameworks/PhysicsKit.framework/PhysicsKit
0x34988000 - 0x3499efff PlugInKit armv7  <e6264267233836f3bcf2ec6046bd5505> /System/Library/PrivateFrameworks/PlugInKit.framework/PlugInKit
0x3499f000 - 0x349a6fff PowerLog armv7  <e307e1f427233b1a83fe7134e49921ba> /System/Library/PrivateFrameworks/PowerLog.framework/PowerLog
0x34c48000 - 0x34c85fff PrintKit armv7  <59173b68b2333f598a17dc7c56f569f4> /System/Library/PrivateFrameworks/PrintKit.framework/PrintKit
0x34c8a000 - 0x34d1efff ProofReader armv7  <b9f28c4e72fc309e907b939d493127b8> /System/Library/PrivateFrameworks/ProofReader.framework/ProofReader
0x34d6e000 - 0x34ddcfff Quagga armv7  <8276ca0278403a45a6ec46bf05b050f3> /System/Library/PrivateFrameworks/Quagga.framework/Quagga
0x351ef000 - 0x35209fff SpringBoardServices armv7  <a0d26a4f5c223babb1f07a0ef4b22966> /System/Library/PrivateFrameworks/SpringBoardServices.framework/SpringBoardServices
0x35574000 - 0x3568cfff StoreServices armv7  <8005c5fd61d13036819a57e965ddf46a> /System/Library/PrivateFrameworks/StoreServices.framework/StoreServices
0x3575e000 - 0x35760fff TCC armv7  <bd765a3a2cc736a0925aeeae1b246631> /System/Library/PrivateFrameworks/TCC.framework/TCC
0x357a7000 - 0x357e4fff TelephonyUtilities armv7  <87c96336d7b433b1bd54f9a0efbc8fed> /System/Library/PrivateFrameworks/TelephonyUtilities.framework/TelephonyUtilities
0x36346000 - 0x3636dfff TextInput armv7  <2c5cc34e04d8316c9eaf54177d191916> /System/Library/PrivateFrameworks/TextInput.framework/TextInput
0x36420000 - 0x364e0fff UIFoundation armv7  <428374a21c553f4892ffd62a9e50f499> /System/Library/PrivateFrameworks/UIFoundation.framework/UIFoundation
0x364fd000 - 0x36500fff UserFS armv7  <897a747e849e3128a52593f4e311783c> /System/Library/PrivateFrameworks/UserFS.framework/UserFS
0x36d5a000 - 0x378cefff WebCore armv7  <4453f473bc773bbb863cde0919a18749> /System/Library/PrivateFrameworks/WebCore.framework/WebCore
0x378cf000 - 0x3798dfff WebKitLegacy armv7  <7a189dc5b02e3ac1b1194b2d3b0dcb08> /System/Library/PrivateFrameworks/WebKitLegacy.framework/WebKitLegacy
0x38509000 - 0x38511fff libAccessibility.dylib armv7  <32190dda98163ebbb0643169fc09b1a5> /usr/lib/libAccessibility.dylib
0x38765000 - 0x3877bfff libCRFSuite.dylib armv7  <0a5fee6c5fb13987a0014c7e6db619aa> /usr/lib/libCRFSuite.dylib
0x387ad000 - 0x388b0fff libFosl_dynamic.dylib armv7  <1b7e35e68bb13a7885642804a30db6cc> /usr/lib/libFosl_dynamic.dylib
0x388ca000 - 0x388e4fff libMobileGestalt.dylib armv7  <1b847e4afb7b367a94bc44a5e393f370> /usr/lib/libMobileGestalt.dylib
0x3890a000 - 0x3890bfff libSystem.B.dylib armv7  <f8d939cc441439e7b2855a6ef4d77c08> /usr/lib/libSystem.B.dylib
0x3897c000 - 0x389c0fff libTelephonyUtilDynamic.dylib armv7  <ad405763cdac370bb854e61772bf394a> /usr/lib/libTelephonyUtilDynamic.dylib
0x38ad0000 - 0x38af2fff libarchive.2.dylib armv7  <4ed6612a4606309b975cbc258ba02c4f> /usr/lib/libarchive.2.dylib
0x38b22000 - 0x38b2efff libbsm.0.dylib armv7  <1d4e1966264c32f38d75e858496cf7f8> /usr/lib/libbsm.0.dylib
0x38b2f000 - 0x38b38fff libbz2.1.0.dylib armv7  <4b7ac694d66732dc9ea3d1d05f012df7> /usr/lib/libbz2.1.0.dylib
0x38b39000 - 0x38b83fff libc++.1.dylib armv7  <31d0bc3ad77a36f78c370c6d154f50d4> /usr/lib/libc++.1.dylib
0x38b84000 - 0x38b9ffff libc++abi.dylib armv7  <8651f217a2113143b778e44e36676179> /usr/lib/libc++abi.dylib
0x38ba1000 - 0x38baefff libcmph.dylib armv7  <73d53cf7c39237759bfd66e8050dd5ae> /usr/lib/libcmph.dylib
0x38bde000 - 0x38bf6fff libextension.dylib armv7  <cd9c4b4d72b9359f9fc0dfb9d4a871bc> /usr/lib/libextension.dylib
0x38d2b000 - 0x38e18fff libiconv.2.dylib armv7  <1b0fd67a32a23c998b57ab552f1562cf> /usr/lib/libiconv.2.dylib
0x38e19000 - 0x38f86fff libicucore.A.dylib armv7  <f4c624598fbf37b0bbee174fac154d91> /usr/lib/libicucore.A.dylib
0x38f93000 - 0x38f93fff liblangid.dylib armv7  <ac2f558933ab3a9bbc0f376833feb5da> /usr/lib/liblangid.dylib
0x38f94000 - 0x38f9efff liblockdown.dylib armv7  <ea95ceffad2531eaa6433591b81c57f3> /usr/lib/liblockdown.dylib
0x38f9f000 - 0x38fb4fff liblzma.5.dylib armv7  <c7608f137e0c3fc892a214899dc3364e> /usr/lib/liblzma.5.dylib
0x3932c000 - 0x39340fff libmis.dylib armv7  <8fea81a19fa8324c8afe054d17b5e529> /usr/lib/libmis.dylib
0x39369000 - 0x39563fff libobjc.A.dylib armv7  <3dfb8b1d89c53df7be0b4d8b17b51b6b> /usr/lib/libobjc.A.dylib
0x39618000 - 0x3962efff libresolv.9.dylib armv7  <dd12c5d28f193f46aa1dcffbecdaaa4e> /usr/lib/libresolv.9.dylib
0x39659000 - 0x396fefff libsqlite3.dylib armv7  <0aacf80bb3573654b41574dc849aeb4f> /usr/lib/libsqlite3.dylib
0x3974c000 - 0x39773fff libtidy.A.dylib armv7  <f42dd1583f533d5ebf43cea1436f8f6f> /usr/lib/libtidy.A.dylib
0x39780000 - 0x39836fff libxml2.2.dylib armv7  <e766a404f6ef3eb482ad641bdc4c5b16> /usr/lib/libxml2.2.dylib
0x39837000 - 0x39858fff libxslt.1.dylib armv7  <a53526d8b3b73c04a3d6b8d6a02b9639> /usr/lib/libxslt.1.dylib
0x39859000 - 0x39865fff libz.1.dylib armv7  <6e6680acca983153ae913c0c17a6c1cd> /usr/lib/libz.1.dylib
0x39866000 - 0x3986afff libcache.dylib armv7  <9961bace92213372ab8863cdfb3a29d6> /usr/lib/system/libcache.dylib
0x3986b000 - 0x39874fff libcommonCrypto.dylib armv7  <1de129ade05e38949012159687a5c051> /usr/lib/system/libcommonCrypto.dylib
0x39875000 - 0x39879fff libcompiler_rt.dylib armv7  <5c4da79950b53afe84ace67fb55833c4> /usr/lib/system/libcompiler_rt.dylib
0x3987a000 - 0x39880fff libcopyfile.dylib armv7  <30fa82f404f63dc1a5d3cc5e3491335d> /usr/lib/system/libcopyfile.dylib
0x39881000 - 0x398cdfff libcorecrypto.dylib armv7  <143b275a4177393f81976175b34308fa> /usr/lib/system/libcorecrypto.dylib
0x398ce000 - 0x39909fff libdispatch.dylib armv7  <429ae50a967737d6b5b4a05c3de017e2> /usr/lib/system/libdispatch.dylib
0x3990a000 - 0x3990bfff libdyld.dylib armv7  <5085dc61f4c03595bb85b15629273fbc> /usr/lib/system/libdyld.dylib
0x3990c000 - 0x3990cfff libkeymgr.dylib armv7  <262bd92918bd3c1c9eb6fcaa3791775e> /usr/lib/system/libkeymgr.dylib
0x3990d000 - 0x3990dfff liblaunch.dylib armv7  <ded7a799dc293fb98cf67551ca948c7f> /usr/lib/system/liblaunch.dylib
0x3990e000 - 0x39911fff libmacho.dylib armv7  <239e78697d683d6991a2a9c0044474b3> /usr/lib/system/libmacho.dylib
0x39912000 - 0x39913fff libremovefile.dylib armv7  <0557b72ca4a3362e884d7cff30e8cafb> /usr/lib/system/libremovefile.dylib
0x39914000 - 0x39925fff libsystem_asl.dylib armv7  <70cf5e1898f13ce18eb75f3458dbd796> /usr/lib/system/libsystem_asl.dylib
0x39926000 - 0x39926fff libsystem_blocks.dylib armv7  <0c999fc715d3379a82ba8c6354709999> /usr/lib/system/libsystem_blocks.dylib
0x39927000 - 0x39989fff libsystem_c.dylib armv7  <20a3892b16363a33903bb2dd2707d505> /usr/lib/system/libsystem_c.dylib
0x3998a000 - 0x3998cfff libsystem_configuration.dylib armv7  <d7cbf07df41a3ea997b79a821ded7d97> /usr/lib/system/libsystem_configuration.dylib
0x3998d000 - 0x3998efff libsystem_coreservices.dylib armv7  <4b9f774a6d3b3ca79c2fe7d1d45b8b22> /usr/lib/system/libsystem_coreservices.dylib
0x3998f000 - 0x3999bfff libsystem_coretls.dylib armv7  <0cc38bb44c223315b61e3dd8ba7c98b6> /usr/lib/system/libsystem_coretls.dylib
0x3999c000 - 0x399a2fff libsystem_dnssd.dylib armv7  <cc3c60b9d86732c7a53f794c7d09debc> /usr/lib/system/libsystem_dnssd.dylib
0x399a3000 - 0x399bbfff libsystem_info.dylib armv7  <30d86b05ccfc364c80512007c565d251> /usr/lib/system/libsystem_info.dylib
0x399bc000 - 0x399d6fff libsystem_kernel.dylib armv7  <fd16698c8ee336f0994797e7b505eccd> /usr/lib/system/libsystem_kernel.dylib
0x399d7000 - 0x399f7fff libsystem_m.dylib armv7  <6ff18932024e3fdb81da9a95cff65be2> /usr/lib/system/libsystem_m.dylib
0x399f8000 - 0x39a0afff libsystem_malloc.dylib armv7  <1cbb1e6f834f352cada0bfe6aae7ced1> /usr/lib/system/libsystem_malloc.dylib
0x39a0b000 - 0x39a38fff libsystem_network.dylib armv7  <d9711d62164c37e084aa419ed251c264> /usr/lib/system/libsystem_network.dylib
0x39a39000 - 0x39a3efff libsystem_networkextension.dylib armv7  <9e633ce07c5631f0b08ea45b745ee3a6> /usr/lib/system/libsystem_networkextension.dylib
0x39a3f000 - 0x39a46fff libsystem_notify.dylib armv7  <2c4117afd36435a795a2c2367723434e> /usr/lib/system/libsystem_notify.dylib
0x39a47000 - 0x39a4cfff libsystem_platform.dylib armv7  <f36fba051a0833fa889eb0bd2cd81875> /usr/lib/system/libsystem_platform.dylib
0x39a4d000 - 0x39a53fff libsystem_pthread.dylib armv7  <365cd5f446003b0288cee279e460bd8a> /usr/lib/system/libsystem_pthread.dylib
0x39a54000 - 0x39a56fff libsystem_sandbox.dylib armv7  <d2a92447495235bb915ea76606b37012> /usr/lib/system/libsystem_sandbox.dylib
0x39a57000 - 0x39a5afff libsystem_stats.dylib armv7  <dfc8455c709d34b39f0b82ca4d497e2d> /usr/lib/system/libsystem_stats.dylib
0x39a5b000 - 0x39a60fff libsystem_trace.dylib armv7  <e82b8baeae2739ee8ce1cb26784a7f79> /usr/lib/system/libsystem_trace.dylib
0x39a61000 - 0x39a61fff libunwind.dylib armv7  <8c41ef1c4e4733ac87472a1910dfe445> /usr/lib/system/libunwind.dylib
0x39a62000 - 0x39a7dfff libxpc.dylib armv7  <84e1bee3b89739dd9a7bd061b6b252c5> /usr/lib/system/libxpc.dylib
]]></log><userid></userid><username></username><contact></contact><installstring>8AB53289-CCFB-4D59-B292-08CDD255894A</installstring><description><![CDATA[]]></description></crash></crashes>';
		if ($xmlstring) {
			$crashes = simplexml_load_string($xmlstring);
			foreach ($crashes->crash as $crash) {
				$appName = (string)$crash->applicationname;
				$log = (string)$crash->log;
				preg_match('/(0x[0-9a-f]+)\s+-\s+0x[0-9a-f]+\s+\+?'.$appName.'\s+(.+)\s+<([0-9a-f]+)>/', $log, $matches);
				if ($matches) {
					$loadAddress = $matches[1];
					$uuid = $matches[3];
					$fileName = Yii::$app->redis->hget('uuid.to.dsym', $uuid);
					if ($fileName) {
						$count = preg_match_all('/\n\d+\s+\S+\s+(0x[0-9a-f]+)\s/', $log, $addressMatches);
						if ($count) {
							exec("atosl -o $fileName -l $loadAddress ".implode(' ', $addressMatches[1]), $output);
							var_export($output);
						}
					}
				}
			}
		} else {
			Yii::$app->response->data['error'] = [
				'code' => 1,
				'message' => 'Missing "xmlstring" param!',
			];
		}
	}

	public function actionLoadDsym()
	{
		$dwarfdump = Yii::$app->request->post('dwarfdump');
		$count = preg_match_all('/UUID: ([0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})\s+/i', $dwarfdump, $matches);
		if (!$count) {
			throw new BadRequestHttpException("Couldn't get uuids");
		}
		$uuids = [];
		foreach ($matches[1] as $uuid) {
			$uuids[] = strtolower(strtr($uuid, ['-' => '']));
		}
		$file = UploadedFile::getInstanceByName('dsym');
		if (!$file) {
			throw new BadRequestHttpException("File not found");
		}
		$uuid = reset($uuids);
		$fileName = date('Y_m_d_') . $uuid;
		if ($file->saveAs(Yii::getAlias('@app/data/dSYMs/' . $fileName))) {
			foreach ($uuids as $uuid) {
				Yii::$app->redis->hset('uuid.to.dsym', $uuid, $fileName);
			}
		} else {
			throw new HttpException(500, "Couldn't save file");
		}
	}
}
