<?php


include_once "/opt/fpp/www/common.php";
//include_once 'functions.inc.php';


$pluginName = basename(dirname(__FILE__));

global $settingInfos;

$DEBUG=false;

if (isset($pluginSettings['DEBUG'])) {
    $DEBUG = $pluginSettings['DEBUG'];
}

if(isset($_POST['submit']))
{
	if($DEBUG)
       print_r($_POST);

    // define some default stuff to go in the config
    //if (!empty($_POST['max31760_addr'])) {
    //   WriteSettingToFile("max31760_addr",urlencode($_POST['max31760_addr']), $pluginName);
    //}

    $pluginConfigFile = $settings['configDirectory'] . "/plugin." . $pluginName;
    if (file_exists($pluginConfigFile)) {
        $pluginSettings = parse_ini_file($pluginConfigFile);
    }
}



?>

<script>

function bindSettingsVisibilityListener() {
    var visProp = getHiddenProp();
    if (visProp) {
      var evtname = visProp.replace(/[H|h]idden/,'') + 'visibilitychange';
      document.addEventListener(evtname, handleSettingsVisibilityChange);
    }
}

function handleSettingsVisibilityChange() {
    if (isHidden() && statusTimeout != null) {
        clearTimeout(statusTimeout);
        statusTimeout = null;
    } else {
        UpdateCurrentTime();
    }
}

var hiddenChildren = {};
function UpdateChildSettingsVisibility() {
    hiddenChildren = {};
    $('.parentSetting').each(function() {
        var fn = 'Update' + $(this).attr('id') + 'Children';
        window[fn](2); // Hide if necessary
    });
    $('.parentSetting').each(function() {
        var fn = 'Update' + $(this).attr('id') + 'Children';
        window[fn](1); // Show if not hidden
    });
}

var statusTimeout = null;
function UpdateCurrentTime(once = false) {
    $.get('api/time', function(data) {
        $('#currentTime').html(data.time);
        if (!once)
            statusTimeout = setTimeout(UpdateCurrentTime, 1000);
    });
}

$(document).ready(function() {
    UpdateChildSettingsVisibility();
    bindSettingsVisibilityListener();
});

</script>


<style>
canvas.matrix {
	height: 371px;
	width: 741px;
}

.atm-top-panel {
	padding-bottom: 0px !important;
}

.atm-middle-panel {
	padding-bottom: 0px !important;
	padding-top: 0px !important;
}

.atm-tool-bottom-panel {
	padding-top: 0px !important;
}

.red {
	background: #ff0000;
}

.green {
	background: #00ff00;
}

.blue {
	background: #0000ff;
}

.yellow {
	background: #ffff00;
}

.orange {
	background: #ff8800;
}

.white {
	background: #ffffff;
}

.black {
	background: #000000;
}

.colorButton {
	-moz-transition: border-color 250ms ease-in-out 0s;
	background-clip: padding-box;
	border: 2px solid rgba(0, 0, 0, 0.25);
	border-radius: 50% 50% 50% 50%;
	cursor: pointer;
	display: inline-block;
	height: 20px;
	margin: 1px 2px;
	width: 20px;
}

</style>

<div class='fppTabs'>
	<div id="atmTabs">
		<ul>
			<li><a href="#tab-atmstatus">Status</a></li>
			<li><a href="#tab-atmglobalsettings">Global Settings</a></li>
<?
            if ($settings['Platform'] == "Raspberry Pi") {
?>
			<li><a href="#tab-atmpipwmsettings">Pi PWM Settings</a></li>
<?
            }
?>
			<li><a href="#tab-atmmax31760settings">MAX31760</a></li>
        </ul>

		<div id= "divSelect" class='ui-tabs-panel atm-top-panel'>
        </div>

        <div id="tab-atmstatus" class='atm-middle-panel'>
			<div id="divText">
                    <table border=0><tr><td valign='top'>
                            Status auto-refresh:
<?
                              PrintSettingCheckbox("Auto refresh", "status_refresh", $restart = 0, $reboot = 0, "ON", "OFF", $pluginName = $pluginName, $callbackName = "");
?>
                            </td>
                        </tr>
                    </table>
                    <p>
					<table border=1><tr><td valign='top'>
<?
                   if ($settings['Platform'] == "Raspberry Pi") {
?>
                        <table border=1>
                         <tr>
                            <td>
                              Bork!
                              <!-- Show pi PWM stuff if this is a pi -->
                            </td>
                         </tr>
                        </table>
<?
                  }
?>

                        <table border=1>
                        <tr>
                            <td>
                            <!-- Show MAX31760 stuff if one or more is detected -->
                            </td>
                        </tr>
                        </table>
                        </td>
                      </tr>
                    </table>
            </div>
        </div>

        <div id="tab-atmglobalsettings" class='atm-middle-panel'>
			<div id= "divDraw">
					<table border=1><tr><td valign='top'>
						<table border=1>
						<tr><td>Bork Fill:</td>
                            <!-- SNMP, alerts, logging, etc -->
							<td>Fibsh</td>
							</tr>
						<tr><td>stuff-atmglobal</td>
						</table>
					</table>
			</div>
		</div>

        <div id="tab-atmpipwmsettings" class='atm-middle-panel'>
<?
             $extraData = "<div class='form-actions'><input type='button' class='buttons' value='Apply Settings' onClick='GetGeoLocation();'></div>";
             PrintPluginSettingGroupTable($pluginName, "PiPWMSettings", $extraData);
?>
             <br>
             <div class="backdrop">
                  <div class="row">
                     <? if ($uiLevel >= 1) { ?>
                        <div class="col-auto"><i class='fas fa-fw fa-graduation-cap ui-level-1'></i> - Advanced Level Setting</div>
                     <? } ?>
                     <? if ($uiLevel >= 2) { ?>
                        <div class="col-auto"><i class='fas fa-fw fa-flask ui-level-2'></i> - Experimental Level Setting</div>
                     <? } ?>
                     <? if ($uiLevel >= 3) { ?>
                        <div class="col-auto"><i class='fas fa-fw fa-code ui-level-3'></i> - Developer Level Setting</div>
                     <? } ?>
                  </div>
             </div>
		</div>

        <div id="tab-atmmax31760settings" class='atm-middle-panel'>
			<div id= "divDraw">
	         <div id="atmmax31760Tabs">
		      <ul>
			   <li><a href="#tab-max31760Tools">Tools</a></li>
			   <li><a href="#tab-max31760Settings">Settings</a></li>
			   <li><a href="#tab-max31760LUT">PWM Look-up Table</a></li>
              </ul>

            <div id="tab-max31760Tools" class='atm-middle-panel'>
             <div id="max31760detection" class="settings">
              <fieldset>
              <legend>MAX31760 Detection</legend>

              <div style="float: right; clear: right;">
              </div>

              <div id="tab-max31760Tools" class='atm-middle-panel'>
              <p>Detecting MAX31760:
<?
 $max31760_devices = array();
 $device_count = 0;
 for ($i = 50; $i <= 57; $i++) {
    $output = "";
    exec("sudo i2cget -y 1 0x" . $i . " 2>&1", $output, $return_val);
    if (implode($output) != "Error: Read failed") {
       $max31760_devices[] = "0x" . $i;
       $device_count++;
       if ($device_count == 1) {
          echo "<span class='good'>Detected on I<sup>2</sup>C address 0x" . $i;
       } else {
          echo ", 0x" . $i;
       }
       if (($i == 57) && ($device_count > 0)) {
          echo "</span><br />\n";
       }
    } 
 }
?>
                </p>
               </fieldset>
              </div>
					<table border=1><tr><td valign='top'>
						<table border=1>
                        <tr><td>MAX31760 Enable: </td><td>
<?
                        // We need to do a callback to something that disables the at24 module by copying max31760.conf to /etc/modprobe.d/ and rebooting
                        PrintSettingCheckbox("MAX31760 Enable", "max31760_enable", $restart = 0, $reboot = 1, "ON", "OFF", $pluginName = $pluginName, $callbackName = "");
?>
                        </td></tr>

						<tr><td>Device Address: </td><td>
<?                      
                        $values = array();
                        $values['None'] = "None";
                        if (!empty($max31760_devices)) {
                           foreach ($max31760_devices as $val) {
                              $values[$val] = $val;
                           }
                        }
                        PrintSettingSelect("Address Select", "max31760_addr", $restart = 0, $reboot = 0, "0x50", $values, $pluginName = $pluginName, $callbackName = "");
?>
                        </td></tr>

						</table>

					</table>
            </div>
            
            <div id="tab-max31760Settings" class='atm-middle-panel'>
<?
               $extraData = "<div class='form-actions'><input type='button' class='buttons' value='Save RAM to EEPROM' onClick='GetGeoLocation();'><input type='button' class='buttons' value='Load EEPROM to RAM' onClick='GetGeoLocation();'></div>";
               PrintPluginSettingGroupTable($pluginName, "MAX31760Settings", $extraData);
?>
               <br>
               <div class="backdrop">
                  <div class="row">
                     <? if ($uiLevel >= 1) { ?>
                        <div class="col-auto"><i class='fas fa-fw fa-graduation-cap ui-level-1'></i> - Advanced Level Setting</div>
                     <? } ?>
                     <? if ($uiLevel >= 2) { ?>
                        <div class="col-auto"><i class='fas fa-fw fa-flask ui-level-2'></i> - Experimental Level Setting</div>
                     <? } ?>
                     <? if ($uiLevel >= 3) { ?>
                        <div class="col-auto"><i class='fas fa-fw fa-code ui-level-3'></i> - Developer Level Setting</div>
                     <? } ?>
                  </div>
               </div>
            </div>

            <div id="tab-max31760LUT" class='atm-middle-panel'>
<?
               $extraData = "<div class='form-actions'><input type='button' class='buttons' value='Save RAM to EEPROM' onClick='GetGeoLocation();'><input type='button' class='buttons' value='Load EEPROM to RAM' onClick='GetGeoLocation();'></div>";
               PrintPluginSettingGroupTable($pluginName, "MAX31760PWMLUTSettings", $extraData);
?>
               <br>
               <div class="backdrop">
                  <div class="row">
                     <? if ($uiLevel >= 1) { ?>
                        <div class="col-auto"><i class='fas fa-fw fa-graduation-cap ui-level-1'></i> - Advanced Level Setting</div>
                     <? } ?>
                     <? if ($uiLevel >= 2) { ?>
                        <div class="col-auto"><i class='fas fa-fw fa-flask ui-level-2'></i> - Experimental Level Setting</div>
                     <? } ?>
                     <? if ($uiLevel >= 3) { ?>
                        <div class="col-auto"><i class='fas fa-fw fa-code ui-level-3'></i> - Developer Level Setting</div>
                     <? } ?>
                  </div>
               </div>
            </div>

			</div>
          </div>
		</div>       
	</div>
</div>

<div id='log'></div>

<script>
	$("#atmTabs").tabs({active: 0, cache: true, spinner: "", fx: { opacity: 'toggle', height: 'toggle' } }); 
	$("#atmmax31760Tabs").tabs({active: 0, cache: true, spinner: "", fx: { opacity: 'toggle', height: 'toggle' } }); 

<?
    foreach ($settingInfos as $sKey => $sData) {
       if (isset($sData['onChange']) && (strpos($sData['name'], "MAX31760") !== false)) {
          echo "function " . $sData['onChange'] . "() {";
          if ($sData['type'] == "checkbox"){
             echo "
    var value = '';
	var checked = 0;
    $('#" . $sKey . "').parent().parent().addClass('loading');
	if ($('#" . $sKey . "').is(':checked')) {
		checked = 1;
		value = '1';
	}
";
             if (isset($sData['children'])) {
                echo "Update$sKey" . "Children(0);\n";
             }
             echo "
            $('#" . $sKey . "').parent().parent().removeClass('loading');
            if (checked)
                $('." . $sKey . "' + 'Child').show();
            else
                $('." . $sKey . "' + 'Child').hide();
";
          }
          echo "
       $.jGrowl('" . $sKey . " changed to ' + $('#" . $sKey . "').val(),{themeState:'success'});
       pluginSettings['" . $sKey . "'] = value;
    }
\n";
       }
    }
?>

</script>
