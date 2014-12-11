<?php
namespace frontend\controllers;

use Yii;
use yii\mongodb\Collection;
use yii\web\Controller;
use yii\web\Response;

/**
 * Api controller
 */
class ApiController extends Controller
{
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
		$acraParams = array (
			'USER_EMAIL' => 'N/A',
			'USER_COMMENT' => '',
			'SETTINGS_GLOBAL' => '',
			'DEVICE_FEATURES' => 'android.hardware.wifi
android.hardware.location.network
android.hardware.location
android.hardware.screen.landscape
android.hardware.screen.portrait
android.hardware.wifi.direct
android.hardware.usb.accessory
android.hardware.bluetooth
android.hardware.microphone
android.software.live_wallpaper
android.hardware.camera.flash
sec.android.mdm
android.hardware.telephony
samsung.software.mediahub.ics
android.software.sip
android.hardware.usb.host
android.hardware.touchscreen.multitouch
android.hardware.sensor.compass
android.hardware.faketouch
android.hardware.camera
com.sec.feature.minimode
android.software.sip.voip
android.hardware.location.gps
android.hardware.telephony.gsm
android.hardware.touchscreen
android.hardware.sensor.accelerometer
glEsVersion = 2.0
',
			'PHONE_MODEL' => 'GT-S5301',
			'SETTINGS_SECURE' => 'ACCESSIBILITY_SCRIPT_INJECTION=0
ACCESSIBILITY_SPEAK_PASSWORD=0
ACCESSIBILITY_WEB_CONTENT_KEY_BINDINGS=0x13=0x01000100; 0x14=0x01010100; 0x15=0x02000001; 0x16=0x02010001; 0x200000013=0x02000601; 0x200000014=0x02010601; 0x200000015=0x03020101; 0x200000016=0x03010201; 0x200000023=0x02000301; 0x200000024=0x02010301; 0x200000037=0x03070201; 0x200000038=0x03000701:0x03010701:0x03020701;
ADB_ENABLED=1
ALLOWED_GEOLOCATION_ORIGINS=http://www.google.co.uk http://www.google.com
ALLOW_MOCK_LOCATION=0
ANDROID_ID=9eae409d166f056
ANR_SHOW_BACKGROUND=0
ASSISTED_GPS_ENABLED=1
BACKUP_ENABLED=1
BACKUP_PROVISIONED=1
BACKUP_TRANSPORT=com.google.android.backup/.BackupTransportService
BLUETOOTH_ON=0
B_ES_DIALOG_DISPLAYED_ON_BOOT=0
B_ES_DIALOG_DISPLAYED_SETTINGS=0
B_ES_OFFL_ENABLED=1
CDMA_CELL_BROADCAST_SMS=1
CDMA_ROAM_DIAL_INTERNATIONAL_ENABLED=0
CDMA_ROAM_DIAL_INTERNATIONAL_FORCED=0
CDMA_ROAM_GUARD_CALL_DOMESTIC=1
CDMA_ROAM_GUARD_CALL_DOMESTIC_FORCED=0
CDMA_ROAM_GUARD_CALL_INTERNATIONAL=1
CDMA_ROAM_GUARD_CALL_INTERNATIONAL_FORCED=0
CDMA_ROAM_GUARD_DATA_DOMESTIC=1
CDMA_ROAM_GUARD_DATA_DOMESTIC_FORCED=0
CDMA_ROAM_GUARD_DATA_INTERNATIONAL_FORCED=0
CDMA_ROAM_GUARD_SMS_INTERNATIONAL=1
CDMA_ROAM_SETTING_CALL_DOMESTIC=1
CDMA_ROAM_SETTING_CALL_DOMESTIC_FORCED=0
CDMA_ROAM_SETTING_CALL_INTERNATIONAL=0
CDMA_ROAM_SETTING_CALL_INTERNATIONAL_FORCED=0
CDMA_ROAM_SETTING_DATA_DOMESTIC=0
CDMA_ROAM_SETTING_DATA_DOMESTIC_FORCED=0
CDMA_ROAM_SETTING_DATA_INTERNATIONAL=0
CDMA_ROAM_SETTING_DATA_INTERNATIONAL_FORCED=0
DATA_NATIONAL_ROAMING_MODE=0
DATA_ROAMING=1
DATA_ROAMING_1=0
DEFAULT_INPUT_METHOD=com.sec.android.inputmethod/.SamsungKeypad
DEVICE_PROVISIONED=1
ENABLED_INPUT_METHODS=com.sec.android.inputmethod/.SamsungKeypad:com.google.android.voicesearch/.ime.VoiceInputMethodService
INPUT_METHODS_SUBTYPE_HISTORY=com.sec.android.inputmethod/.SamsungKeypad;-1
INSTALL_NON_MARKET_APPS=0
KDDI_CPA_ON=0
LAST_SETUP_SHOWN=eclair_1
LOCATION_PDR_ENABLED=0
LOCATION_PROVIDERS_ALLOWED=network
LOCK_MOTION_TILT_TO_UNLOCK=0
LOCK_PATTERN_ENABLED=1
LOCK_PATTERN_TACTILE_FEEDBACK_ENABLED=0
LOCK_PATTERN_VISIBLE=1
LOCK_SCREEN_LOCK_AFTER_TIMEOUT=0
LOCK_SCREEN_OWNER_INFO=Hum comida ..
LOCK_SCREEN_OWNER_INFO_ENABLED=1
LOCK_SIGNATURE_VISIBLE=1
LONG_PRESS_TIMEOUT=500
MOBILE_DATA=0
MOUNT_PLAY_NOTIFICATION_SND=1
MOUNT_UMS_AUTOSTART=0
MOUNT_UMS_NOTIFY_ENABLED=1
MOUNT_UMS_PROMPT=1
NETWORK_PREFERENCE=1
PREFERRED_NETWORK_MODE=0
SELECTED_INPUT_METHOD_SUBTYPE=-1
SEND_ACTION_APP_ERROR=1
SIM1_CELL_BROADCAST_ENABLE=0
SIM2_CELL_BROADCAST_ENABLE=0
TETHER_DUN_REQUIRED=0
THROTTLE_RESET_DAY=23
TOUCH_EXPLORATION_ENABLED=0
TTS_DEFAULT_RATE=100
TTS_DEFAULT_SYNTH=com.google.android.tts
USB_MASS_STORAGE_ENABLED=1
USB_SETTING_MODE=0
VOICE_RECOGNITION_SERVICE=com.google.android.voicesearch/.GoogleRecognitionService
WEB_AUTOFILL_QUERY_URL=http://android.clients.google.com/proxy/webautofill
WIFI_COUNTRY_CODE=br
WIFI_NETWORKS_AVAILABLE_NOTIFICATION_ON=1
WIFI_ON=1
WIFI_P2P_SSID=Android_9eae
WIFI_SAVED_STATE=0
',
			'INSTALLATION_ID' => '78529889-7224-4375-9be3-a212e8cc23bc',
			'SETTINGS_SYSTEM' => 'ACCELEROMETER_ROTATION=0
ACCELEROMETER_ROTATION_SECOND=0
AIRPLANE_MODE_ON=0
AIRPLANE_MODE_RADIOS=cell,bluetooth,wifi,nfc
AIRPLANE_MODE_TOGGLEABLE_RADIOS=bluetooth,wifi,nfc
AIR_MOTION_CALL_ACCEPT=0
AIR_MOTION_CLIP=0
AIR_MOTION_GLANCE_VIEW=0
AIR_MOTION_ITEM_MOVE=0
AIR_MOTION_NOTE_SWAP=0
AIR_MOTION_SCROLL=0
AIR_MOTION_WEB_NAVIGATE=0
ALARM_ALERT=content://media/external/audio/media/7474
ALWAYS_FINISH_ACTIVITIES=0
ASSISTED_DIALING=0
AUDIO_BALANCE=50
AUTO_TIME=1
AUTO_TIME_ZONE=0
BUTTON_KEY_LIGHT=1500
CALL_AUTO_RETRY=0
CAR_DOCK_SOUND=/system/media/audio/ui/Dock.ogg
CAR_UNDOCK_SOUND=/system/media/audio/ui/Undock.ogg
CLOCK_POSITION=0
CONTENTS_TYPE=0
CONTEXTUAL_PAGE=1
CONTEXTUAL_PAGE_CAR_CRADLE=1
CONTEXTUAL_PAGE_DESK_CRADLE=1
CONTEXTUAL_PAGE_EARPHONE=1
CONTEXTUAL_PAGE_ROAMING=0
CONTEXTUAL_PAGE_S_PEN=0
COUNTRY_CODE=011
CRADLE_CONNECT=0
CRADLE_ENABLE=0
CRADLE_LAUNCH=1
DATE_FORMAT=dd-MM-yyyy
DB_KEY_DRIVING_MODE_ON=0
DEFAULT_VIBRATION_PATTERN=content://com.android.settings.personalvibration.PersonalVibrationProvider/1
DESK_DOCK_SOUND=/system/media/audio/ui/Dock.ogg
DESK_UNDOCK_SOUND=/system/media/audio/ui/Undock.ogg
DIM_SCREEN=1
DISPLAY_BATTERY_LEVEL=1
DOCK_SOUNDS_ENABLED=1
DORMANT_ALLOW_LIST=None
DORMANT_ALWAYS=0
DORMANT_DISABLE_ALARM_AND_TIMER=0
DORMANT_DISABLE_INCOMING_CALLS=0
DORMANT_DISABLE_LED_INDICATOR=0
DORMANT_DISABLE_NOTIFICATIONS=0
DORMANT_END_HOUR=0
DORMANT_END_MIN=0
DORMANT_START_HOUR=0
DORMANT_START_MIN=0
DORMANT_SWITCH_ONOFF=0
DRIVING_MODE_ALARM_NOTIFICATION=1
DRIVING_MODE_EMAIL_NOTIFICATION=1
DRIVING_MODE_INCOMING_CALL_NOTIFICATION=1
DRIVING_MODE_MESSAGE_CONTENTS=1
DRIVING_MODE_MESSAGE_NOTIFICATION=1
DRIVING_MODE_SCHEDULE_NOTIFICATION=1
DRIVING_MODE_UNLOCK_SCREEN_CONTENTS=0
DRIVING_MODE_VOICE_MAIL_NOTIFICATION=1
DTMF_TONE_TYPE_WHEN_DIALING=0
DTMF_TONE_WHEN_DIALING=0
EMERGENCY_TONE=0
FONT_SCALE=1.0
FONT_SIZE=2
GLANCE_VIEW_BATTERY_CHARGING_INFO=0
GLANCE_VIEW_MISSED_CALL=0
GLANCE_VIEW_NEAREST_ALARM=0
GLANCE_VIEW_NEW_MESSAGE=0
GLANCE_VIEW_NOW_PLAYING_MUSIC=0
GLANCE_VIEW_STATUS_BAR=0
GLANCE_VIEW_TIME_DATE=0
HAPTIC_FEEDBACK_ENABLED=1
HEARING_AID=0
HELP_OVERLAY_CHECKED=111
HIGH_CONTRAST=0
INFORMATION_TICKER=0
INFORMATION_TICKER_AUTO_REFRESH=1
INTELLIGENT_ROTATION_MODE=0
INTELLIGENT_SCREEN_MODE=1
INTELLIGENT_SLEEP_MODE=0
LED_INDICATOR_CHARING=1
LED_INDICATOR_INCOMING_NOTIFICATION=0
LED_INDICATOR_MISSED_EVENT=1
LED_INDICATOR_VOICE_RECORDING=1
LOCKSCREEN_SHORTCUT_BOX=0
LOCKSCREEN_SOUNDS_ENABLED=0
LOCK_PCW_ENABLED=10
LOCK_PCW_PASSWORD=
LOCK_SCREEN_FACE_WITH_VOICE=0
LOCK_SCREEN_SHORTCUT=1
LOCK_SCREEN_SHORTCUT_APP_LIST=com.android.contacts/com.android.contacts.activities.DialtactsActivity;com.sec.chaton/com.sec.chaton.HomeActivity;com.android.browser/com.android.browser.BrowserActivity;com.sec.android.app.camera/com.sec.android.app.camera.Camera;
LOCK_SCREEN_SHORTCUT_NUMBER_OF_APPS=4
LOCK_SCREEN_WALLPAPER=1
LOCK_SOUND=/system/media/audio/ui/Lock.ogg
LOW_BATTERY_SOUND=/system/media/audio/ui/LowBattery.ogg
MEDIA_BUTTON_RECEIVER=com.sec.android.app.music/com.sec.android.app.music.MediaButtonIntentReceiver
MODE_RINGER=0
MODE_RINGER_STREAMS_AFFECTED=166
MOTION_DOUBLE_TAP=0
MOTION_ENGINE=0
MOTION_OVERTURN=0
MOTION_PANNING=0
MOTION_PANNING_SENSITIVITY=5
MOTION_PAN_TO_BROWSE_IMAGE=0
MOTION_PAN_TO_BROWSE_IMAGE_SENSITIVITY=5
MOTION_PICK_UP=0
MOTION_PICK_UP_TO_CALL_OUT=0
MOTION_SHAKE=0
MOTION_SHAKE_REFRESH_GUIDE_SHOW_AGAIN=0
MOTION_SHAKE_SCAN_GUIDE_SHOW_AGAIN=0
MOTION_TILT_TO_SCROLL_LIST=0
MOTION_TILT_TO_SCROLL_LIST_SENSITIVITY=5
MOTION_UNLOCK_CAMERA_SHORT_CUT=0
MOTION_ZOOMING=0
MOTION_ZOOMING_GUIDE_SHOW_AGAIN=0
MOTION_ZOOMING_SENSITIVITY=5
MUTE_STREAMS_AFFECTED=46
NEXT_ALARM_FORMATTED=Qua 9:45
NOTIFICATION_LIGHT_PULSE=0
NOTIFICATION_SOUND=content://media/internal/audio/media/30
ONSCREEN_KEYPAD=1
PEN_APPICATIONS=Shortcuts toolbar
PEN_DETACHMENT_ALERT=1
PEN_DETACHMENT_NOTIFICATION=/system/media/audio/ui/Pen_att_noti1.ogg,/system/media/audio/ui/Pen_det_noti1.ogg
PEN_DETECT_MODE_DISABLED=0
PEN_GESTURE_GUIDE=1
PEN_HOVERING=0
PEN_HOVERING_ICON_LABEL=0
PEN_HOVERING_INFORMATION_PREVIEW=0
PEN_HOVERING_LIST_SCROLL=0
PEN_HOVERING_RIPPLE_EFFECT=0
PHONE1_ON=1
PHONE2_ON=1
POINTER_LOCATION=0
POINTER_SPEED=7
POWER_KEY_HOLD=0
POWER_SAVING_MODE=1
POWER_SOUNDS_ENABLED=1
PSM_BACKGROUND=1
PSM_BROWSER_COLOR=colorize3
PSM_CPU=1
PSM_DISPLAY=1
PSM_HAPTIC=1
PSM_SWITCH=0
QUICK_LAUNCH_APP=1
RINGTONE=content://media/external/audio/media/36174
RINGTONE_AFTER_VIBRATION=0
SCREEN_BRIGHTNESS=35
SCREEN_BRIGHTNESS_MODE=0
SCREEN_OFF_TIMEOUT=60000
SCREEN_ZOOM=0
SEEK_POSITION=50
SELECT_ICON_1=0
SELECT_ICON_2=1
SELECT_NAME_1=SIM 1
SELECT_NAME_2=SIM 2
SHOPDEMO=0
SHOW_TOUCHES=0
SIM1_VALUE=0
SIM2_VALUE=0
SLIDING_SPEED=1
SOUND_EFFECTS_ENABLED=0
SOUND_PROFILE_EDIT_MODE=0
SOUND_PROFILE_MODE=0
SPLITEVIEW_MODE_CALENDAR=1
SPLITEVIEW_MODE_IM=1
SPLITEVIEW_MODE_MEMO=1
SPLITEVIEW_MODE_MESSAGE=1
SPLITEVIEW_MODE_MUSIC=1
SPLITEVIEW_MODE_MYFILES=1
SPLITEVIEW_MODE_PHONE=1
SPLITEVIEW_MODE_SOCIALHUB=1
STAY_ON_WHILE_PLUGGED_IN=0
SURFACE_PALM_SWIPE=0
SURFACE_PALM_TOUCH=0
SURFACE_TAP_AND_TWIST=0
TEXT_SHOW_PASSWORD=1
TIME_12_24=24
TORCH_LIGHT=0
TRANSITION_ANIMATION_SCALE=0.5
TTY_MODE=0
UART_APCP_MODE=0
UNA_SETTING=1
UNLOCK_SOUND=/system/media/audio/ui/Unlock.ogg
USB_APCP_MODE=1
USER_ROTATION=0
VIBRATE_IN_SILENT=0
VIBRATE_ON=10
VIB_FEEDBACK_MAGNITUDE=3
VIB_NOTIFICATION_MAGNITUDE=5
VIB_RECVCALL_MAGNITUDE=5
VOLUME_ALARM=5
VOLUME_BLUETOOTH_SCO=7
VOLUME_HPH_MUSIC=6
VOLUME_MUSIC=5
VOLUME_NOTIFICATION=0
VOLUME_RING=0
VOLUME_SYSTEM=0
VOLUME_VOICE=5
VOLUME_WAITING_TONE=2
WIFI_SLEEP_POLICY=2
WINDOW_ANIMATION_SCALE=0.5
WITH_CIRCLE=0
',
			'SHARED_PREFERENCES' => 'default.acra.lastVersionNr=33

',
			'IS_SILENT' => '',
			'ANDROID_VERSION' => '4.0.4',
			'PACKAGE_NAME' => 'com.camlyapp.Camly',
			'APP_VERSION_CODE' => '33',
			'CRASH_CONFIGURATION' => 'FlipFont=0
compatScreenHeightDp=401
compatScreenWidthDp=320
compatSmallestScreenWidthDp=320
fontScale=1.0
hardKeyboardHidden=HARDKEYBOARDHIDDEN_YES
keyboard=KEYBOARD_NOKEYS
keyboardHidden=KEYBOARDHIDDEN_NO
locale=pt_BR
mcc=724
mnc=5
navigation=NAVIGATION_NONAV
navigationHidden=NAVIGATIONHIDDEN_YES
orientation=ORIENTATION_PORTRAIT
screenHeightDp=401
screenLayout=SCREENLAYOUT_SIZE_SMALL+SCREENLAYOUT_LONG_NO
screenWidthDp=320
seq=5
smallestScreenWidthDp=320
textLayoutDirection=0
touchscreen=TOUCHSCREEN_FINGER
uiMode=UI_MODE_TYPE_NORMAL+UI_MODE_NIGHT_NO
userSetLocale=false
',
			'USER_CRASH_DATE' => '2014-11-26T01:11:47.000-02:00',
			'DUMPSYS_MEMINFO' => 'Permission Denial: can\'t dump meminfo from from pid=31446, uid=10103 without permission android.permission.DUMP
',
			'BUILD' => 'BOARD=rhea
BOOTLOADER=unknown
BRAND=samsung
CPU_ABI=armeabi-v7a
CPU_ABI2=armeabi
DEVICE=coriplus
DISPLAY=IMM76D.S5301XXAMK1
FINGERPRINT=samsung/coriplusxx/coriplus:4.0.4/IMM76D/S5301XXAMK1:user/release-keys
HARDWARE=rhea_ss_coriplus
HOST=DELL318
ID=IMM76D
IS_DEBUGGABLE=false
MANUFACTURER=samsung
MODEL=GT-S5301
PRODUCT=coriplusxx
RADIO=unknown
SERIAL=47905325a01d5000
TAGS=release-keys
TIME=1385009875000
TYPE=user
UNKNOWN=unknown
USER=dpi
VERSION.CODENAME=REL
VERSION.INCREMENTAL=S5301XXAMK1
VERSION.RELEASE=4.0.4
VERSION.RESOURCES_SDK_INT=15
VERSION.SDK=15
VERSION.SDK_INT=15
',
			'STACK_TRACE' => 'java.lang.RuntimeException: Canvas: trying to use a recycled bitmap android.graphics.Bitmap@65e58390
	at android.graphics.Canvas.throwIfRecycled(Canvas.java:1038)
	at android.graphics.Canvas.drawBitmap(Canvas.java:1078)
	at com.camlyapp.Camly.ui.edit.view.size.ImageViewTouchScaledSquare.getCroppedImage(ImageViewTouchScaledSquare.java:92)
	at com.camlyapp.Camly.ui.edit.view.size.ImageViewTouchScaledSquare.applay(ImageViewTouchScaledSquare.java:33)
	at com.camlyapp.Camly.ui.edit.view.size.SizeViewFragment.onApplay(SizeViewFragment.java:318)
	at com.camlyapp.Camly.ui.edit.view.size.SizeViewFragment.onClick(SizeViewFragment.java:349)
	at android.view.View.performClick(View.java:3660)
	at android.view.View$PerformClick.run(View.java:14427)
	at android.os.Handler.handleCallback(Handler.java:605)
	at android.os.Handler.dispatchMessage(Handler.java:92)
	at android.os.Looper.loop(Looper.java:137)
	at android.app.ActivityThread.main(ActivityThread.java:4517)
	at java.lang.reflect.Method.invokeNative(Native Method)
	at java.lang.reflect.Method.invoke(Method.java:511)
	at com.android.internal.os.ZygoteInit$MethodAndArgsCaller.run(ZygoteInit.java:995)
	at com.android.internal.os.ZygoteInit.main(ZygoteInit.java:762)
	at dalvik.system.NativeStart.main(Native Method)
',
			'PRODUCT' => 'coriplusxx',
			'DISPLAY' => '0.height=320
0.orientation=0
0.pixelFormat=5
0.getRealSize=[240,320]
0.rectSize=[0,0,240,320]
0.refreshRate=92.0
0.rotation=ROTATION_0
0.getSize=[240,320]
0.width=240
',
			'LOGCAT' => '',
			'APP_VERSION_NAME' => '1.5.4',
			'AVAILABLE_MEM_SIZE' => '935788544',
			'USER_APP_START_DATE' => '2014-11-26T01:02:24.000-02:00',
			'CUSTOM_DATA' => 'CAMLY_UID = 6ebd50b7891e8dbd20acedb33f0e73ed
',
			'BRAND' => 'samsung',
			'INITIAL_CONFIGURATION' => 'FlipFont=0
compatScreenHeightDp=401
compatScreenWidthDp=320
compatSmallestScreenWidthDp=320
fontScale=1.0
hardKeyboardHidden=HARDKEYBOARDHIDDEN_YES
keyboard=KEYBOARD_NOKEYS
keyboardHidden=KEYBOARDHIDDEN_NO
locale=pt_BR
mcc=724
mnc=5
navigation=NAVIGATION_NONAV
navigationHidden=NAVIGATIONHIDDEN_YES
orientation=ORIENTATION_PORTRAIT
screenHeightDp=401
screenLayout=SCREENLAYOUT_SIZE_SMALL+SCREENLAYOUT_LONG_NO
screenWidthDp=320
seq=5
smallestScreenWidthDp=320
textLayoutDirection=0
touchscreen=TOUCHSCREEN_FINGER
uiMode=UI_MODE_TYPE_NORMAL+UI_MODE_NIGHT_NO
userSetLocale=false
',
			'TOTAL_MEM_SIZE' => '2200907776',
			'FILE_PATH' => '/data/data/com.camlyapp.Camly/files',
			'ENVIRONMENT' => 'getAndroidSecureContainerDirectory=/mnt/asec
getDataDirectory=/data
getDownloadCacheDirectory=/cache
getExternalStorageAndroidDataDir=/mnt/sdcard/Android/data
getExternalStorageDirectory=/mnt/sdcard
getExternalStorageState=mounted
getRootDirectory=/system
getSecureDataDirectory=/data
getSystemSecureDirectory=/data/system
isEncryptedFilesystemEnabled=false
isExternalStorageEmulated=true
isExternalStorageRemovable=false
',
			'REPORT_ID' => 'd5522ada-0a06-40d0-9660-e63a7190aa23',
		);
		if (isset($acraParams['PACKAGE_NAME'], $acraParams['STACK_TRACE'], $acraParams['APP_VERSION_NAME'], $acraParams['APP_VERSION_CODE'], $acraParams['USER_CRASH_DATE'])) {
			$fullInfo = json_encode($acraParams);
			$packageName = $acraParams['PACKAGE_NAME'];
			$stackTrace = $acraParams['STACK_TRACE'];
			$stackTrace = preg_replace('/Bitmap@.+/i', 'Bitmap', $stackTrace, 1);
			$stack = explode("\n", $stackTrace);
			$stackTraceMini = $stack[0];
			foreach ($stack as $line) {
				if (strpos($line, 'at '.$packageName) !== false) {
					$stackTraceMini .= "\n...".$line;
					break;
				}
			}
			$hashMini = md5($stackTraceMini);
			$hash = md5($stackTrace);
			$appVersionName = $acraParams['APP_VERSION_NAME'];
			$appVersionCode = (int)$acraParams['APP_VERSION_CODE'];
			$userCrashDate = new \MongoDate(strtotime($acraParams['USER_CRASH_DATE']));
			/* @var $collection Collection */
			$collection = Yii::$app->mongodb->getCollection('crash');
			$collection->insert([
				'package_name' => $packageName,
				'hash' => $hash,
				'hash_mini' => $hashMini,
				'stack_trace' => $stackTrace,
				'stack_trace_mini' => $stackTraceMini,
				'app_version_name' => $appVersionName,
				'app_version_code' => $appVersionCode,
				'user_crash_date' => $userCrashDate,
				'full_info' => $fullInfo
			]);
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
}
