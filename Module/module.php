<?

function issetDef(&$var, $default = null)
{

    return isset($var) ? $var : $default;

}

function toHex($dataString)
{
    $hexstr = unpack('H*', $dataString);
    return array_shift($hexstr);
}

function hex2int($data)
{

    $result = intval(ord($data[0]) * 256 + ord($data[1]));
    if ($result > 0x7FF) {
        $result = $result - 0xFFFF;
    }

    return $result;
}


/**
 *
 * Author: Timo Beyel
 *
 * Description:
 */
class LWZ303Command
{
    public $command_id = "00";

    public $properties;

    public function parse($data)
    {

    }
}

class LWZ303Command_Version extends LWZ303Command
{
    function __construct()
    {
        $this->command_id = "FD";
    }

    public function parse($data)
    {
        $version = number_format((ord($data[0]) * 256 + ord($data[1])) / 100, 2, ".", "");
        $this->properties = array(
            array(
                "id"    => "version",
                "name"  => "Version",
                "value" => $version
            )
        );
    }
}

/**
 * Class LWZ303Command_Global
 *
 * Example:
 * 00 00
 * 00 21  Outside Temperatur
 * 01 3F  Flow Temp
 * 01 09  Return Temp
 * 02 F1  HotGas Temp
 * 01 D0  HotWater Temp
 * 00 00  Flow Temp HC2
 * 00 00  Inside Temp
 * FF 0C  Evaporator Temp
 * 01 3B  Condenser Temp
 * 19 10  Bitmask
 * 10  Ouput Vent Power
 * 2D  Input Vent Power
 * 00  Main Vent Power
 * CC
 * CB Ouput Vent Speed
 * 17 Input Vent Speed
 * 00 Main Vent Speed
 * 01
 * 00 23 Outside Temp Filtered
 * 00 00
 * 00 00
 * 00 02
 * 00 01
 * 00 10 03
 */
class LWZ303Command_Global extends LWZ303Command
{

    function __construct()
    {
        $this->command_id = "FB";
    }

    public function parse($data)
    {
        $outside_temp = number_format(hex2int(substr($data, 2, 2)) / 10, 2, ",", "");
        $flow_temp = number_format(hex2int(substr($data, 4, 2)) / 10, 2, ",", "");
        $return_temp = number_format(hex2int(substr($data, 6, 2)) / 10, 2, ",", "");
        $hotgas_temp = number_format(hex2int(substr($data, 8, 2)) / 10, 2, ",", "");
        $hotwater_temp = number_format(hex2int(substr($data, 10, 2)) / 10, 2, ",", "");
        $flow_temp2 = number_format(hex2int(substr($data, 12, 2)) / 10, 2, ",", "");
        $inside_temp = number_format(hex2int(substr($data, 14, 2)) / 10, 2, ",", "");
        $evep_temp = number_format(hex2int(substr($data, 16, 2)) / 10, 2, ",", "");
        $condens_temp = number_format(hex2int(substr($data, 18, 2)) / 10, 2, ",", "");


        $outputVent_speed = ord($data[26]);
        $inputVent_speed = ord($data[27]);
        $mainVent_speed = ord($data[28]);

        $this->properties = array(
            array(
                "id"    => "outside_temp",
                "name"  => "Außentemperatur",
                "value" => $outside_temp
            ),
            array(
                "id"    => "hotwatertemp",
                "name"  => "Warmwassertemperatur",
                "value" => $hotwater_temp
            ),
            array(
                "id"    => "flowtemp",
                "name"  => "Vorlauftemperatur",
                "value" => $flow_temp
            ),
            array(
                "id"    => "returntemp",
                "name"  => "Rücklauftemperatur",
                "value" => $return_temp
            ),
            array(
                "id"    => "hotgas",
                "name"  => "Heißgastemperatur",
                "value" => $hotgas_temp
            ),
            array(
                "id"    => "insidetemp",
                "name"  => "Raumtemperatur",
                "value" => $inside_temp
            ),
            array(
                "id"    => "eveptemp",
                "name"  => "Verdampfertemperatur",
                "value" => $evep_temp
            ),
            array(
                "id"    => "condenstemp",
                "name"  => "Verflüssigertemperatur",
                "value" => $condens_temp
            ),
            
            array(
                "id"    => "outvent_speed",
                "name"  => "Drehzahl Ablüfter",
                "value" => $outputVent_speed
            ),
            array(
                "id"    => "invent_speed",
                "name"  => "Drehzahl Zulüfter",
                "value" => $inputVent_speed
            ),
            array(
                "id"    => "mainvent_speed",
                "name"  => "Drehzahl Fortlüfter",
                "value" => $mainVent_speed
            )

        );
    }
}

/*
Example:

01      Nr. of errrors
01      Error Index (1-Nr. of errors)
00 0F   Error code
00 9B   Time (1:55)
05 DD   Date (15.01)
*/

class LWZ303Command_Errorlog extends LWZ303Command
{
    private $error_codes = array(
        0  => "Kein Fehler",
        1  => "Fehler Anode",
        3  => "Fehler Hochdruckwächter",
        4  => "Fehler Niederdruckwächter",
        5  => "Fehler Abluftfühler",
        6  => "Fehler Zuluftfühler",
        7  => "Fehler Fortluftfühler",
        15 => "Fehler Warmwassertemperatur",
        17 => "Abtauzeit wird überschritten",
        20 => "Fehler Solarfühler",
        21 => "Fehler Außentemperaturfühler",
        22 => "Fehler Heißgastemperaturfühler",
        23 => "Fehler Verflüssigertemperaturfühler",
        24 => "Fehler Verdampfertemperaturfühler",
        26 => "Fehler Rücklauftemperaturfühler",
        28 => "Fehler Vorlauftemperaturfühler",
        29 => "Fehler Warmwassertemperaturfühler"
    );

    function __construct()
    {
        $this->command_id = "D1";
    }

    public function parse($data)
    {
        $error_count = ord($data[0]);
        IPS_LogMessage("LWZ303Command_Errorlog", "Errors found" . $error_count);
        $pos = 2;
        $this->properties = array(
            array(
                "id"    => "errorcount",
                "name"  => "Fehleranzahl",
                "value" => $error_count
            )
        );
        for ($i = 0; $i < $error_count; $i++) {
            $index = $i + 1;

            $error_code = ord($data[$pos]) * 256 + ord($data[$pos + 1]);
            $pos += 2;
            $error_time = number_format((ord($data[$pos]) * 256 + ord($data[$pos + 1])) / 100, 2, ":", "");
            $pos += 2;
            $error_date = number_format((ord($data[$pos]) * 256 + ord($data[$pos + 1])) / 100, 2, ".", "");
            $pos += 2;

            $error_name = issetDef($this->error_codes[$error_code], "");


            $this->properties[] = array(
                "id"    => "errornr" . $index,
                "name"  => "Fehler" . $index,
                "value" => $error_code . " - " . $error_name . " "
            );

            $this->properties[] = array(
                "id"    => "errordate" . $index,
                "name"  => "Fehler" . $index . "-Zeit",
                "value" => $error_date . " " . $error_time . " Uhr"
            );

        }
    }
}

class LWZ303 extends IPSModule
{

    const STATE_WAIT_NONE = "NONE";
    const STATE_WAIT_ACK_COMMAND_INIT = "WAIT_ACK_COMMAND_INIT";
    const STATE_WAIT_ACK_COMMAND = "WAIT_ACK_COMMAND";
    const STATE_WAIT_HEADER = "WAIT_HEADER";
    const STATE_WAIT_DATA = "WAIT_DATA";

    const command_readytosend = "02"; //Send in order to initialize communication
    const command_ack = "10"; //received, to confirm, communication is setup

    const response_header_get_ok = "0100";           //Header for received data, if everything is ok
    const response_header_error_crc = "0102";        //Header for received data, if request had wrong CRC
    const response_header_error_unknown_cmd = "0103"; //Header for received data, if the command is unknown
    const response_header_error_unknown_req = "0104"; //Header for received data, if the request is wrong
    const response_footer = "1003";

    const request_header_get = "0100";
    const request_header_set = "0108";
    const request_footer = "1003";

    const registered_commands = array(
        "LWZ303Command_Version",
        "LWZ303Command_Errorlog",
        "LWZ303Command_Global"
    );

    /**
     * Structure of Request
     *
     * 01 00                Header
     * CRC                  CRC of Header and command
     * CMD                  Command
     * (DATA)               Depending on command, optional data
     * 10 03                Footer
     *
     *
     * Structure of Reply
     *
     * 01 00                Command received, or 01 02 etc. if an error occured
     * CRC                  CRC of Header and reply data
     * CMD                  Command-ID of the requested command
     * (DATA)               Actual command data
     * 10 03                Footer
     */


    public function Create()
    {
        //Never delete this line!
        parent::Create();
        $this->RegisterVariableString("buffer", "Received Data");
        $this->RegisterVariableString("state", "Current state");
        $this->RegisterVariableString("command", "Command in queue");
        $this->RegisterVariableString("response", "Response");

        $this->ConnectParent("{6DC3D946-0D31-450F-A8C6-C42DB8D7D4F1}");

    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();
        SetValue($this->GetIDForIdent("buffer"), "");
    }

    private function createChecksum($str)
    {
        $chk = 0;
        for ($k = 0; $k < strlen($str); $k = $k + 1) {
            $o = ord(substr($str, $k, 1));
            $chk = $chk + $o;
        }
        $ret = chr($chk % 256);

        return $ret;
    }

    private function escapeData($str)
    {
        $str = str_replace(chr(0x10), chr(0x10) . chr(0x10), $str);
        $str = str_replace(chr(0x2B), chr(0x2B) . chr(0x18), $str);

        return $str;
    }

    private function unescapeData($str)
    {
        $str = str_replace(chr(0x10) . chr(0x10), chr(0x10), $str);
        $str = str_replace(chr(0x2B) . chr(0x18), chr(0x18), $str);

        return $str;
    }


    private function initializeCommand()
    {
        SetValue($this->GetIDForIdent("state"), LWZ303::STATE_WAIT_ACK_COMMAND_INIT);
        $this->sendCommandtoParent(LWZ303::command_readytosend);

    }

    public function SendCommand($command, $data)
    {

        $classname = "LWZ303Command_" . $command;
        $class = new $classname();
        $code = hex2bin($class->command_id);
        $header = hex2bin(LWZ303::request_header_get);
        $footer = hex2bin(LWZ303::request_footer);
        $checksum = $this->escapeData($this->createChecksum($header . $code));
        $code = $this->escapeData($code);

        $command_in_queue = toHex($header . $checksum . $code . $footer);

        IPS_LogMessage("LWZ303", "Queing command:" . $command_in_queue);
        SetValue($this->GetIDForIdent("command"), $command_in_queue);
        $this->initializeCommand();

    }


    private function sendCommandInQueue()
    {
        SetValue($this->GetIDForIdent("state"), LWZ303::STATE_WAIT_ACK_COMMAND);
        $command_in_queue = GetValue($this->GetIDForIdent("command"));
        $this->sendCommandtoParent($command_in_queue);
        SetValue($this->GetIDForIdent("command"), "");
    }

    private function sendCommandtoParent($hex_data)
    {
        SetValue($this->GetIDForIdent("buffer"), "");
        $json = json_encode(Array(
                                "DataID" => "{79827379-F36E-4ADA-8A95-5F8D1DC92FA9}",
                                "Buffer" => utf8_encode(hex2bin($hex_data))
                            ));


        IPS_LogMessage("LWZ303", "Sending data to parent:" . $json);
        $this->SendDataToParent($json);
    }

    public function RequestAction($Ident, $Value)
    {
        $this->SendDebug("LWZ-Request", "Setting $Ident to $Value", 0);
        switch ($Ident) {

            default:
                throw new Exception("Invalid Ident");
        }
    }

    // Beispiel innerhalb einer Geräte/Device Instanz
    public function ReceiveData($JSONString)
    {

        // Empfangene Daten vom Gateway/Splitter
        $data = json_decode($JSONString);
        $decoded_data = toHex(utf8_decode($data->Buffer));

        $buffer = GetValue($this->GetIDForIdent("buffer"));
        $buffer .= $decoded_data;
        SetValue($this->GetIDForIdent("buffer"), $buffer);
        IPS_LogMessage("LWZ303", "Current Buffer:" . $buffer);

        $this->parseBuffer();
    }

    private function clearBuffer($count)
    {
        if ($count == -1) {
            SetValue($this->GetIDForIdent("buffer"), "");
        }
        else {
            $buffer = GetValue($this->GetIDForIdent("buffer"));
            $buffer = substr($buffer, $count);
            if ($buffer === false) {
                $buffer = "";
            };
            SetValue($this->GetIDForIdent("buffer"), $buffer);
        }
    }

    private function resetCommunication()
    {
        SetValue($this->GetIDForIdent("buffer"), "");
        SetValue($this->GetIDForIdent("state"), LWZ303::STATE_WAIT_NONE);
    }

    private function parseCommandReply($command, $data)
    {
        foreach (LWZ303::registered_commands as $item) {
            $class = new $item();
            if (strtolower($class->command_id) == strtolower(toHex($command))) {
                $class->parse($data);
                foreach ($class->properties as $property) {
                    $id = $property["id"];
                    $name = $property["name"];
                    $value = $property["value"];
                    $this->RegisterVariableString($id, $name);
                    SetValue($this->GetIDForIdent($id), $value);
                }
            }
        }
    }

    private function parseReply($reply)
    {
        IPS_LogMessage("LWZ303", "PARSING REPLY:" . $reply);
        $rawData = $this->unescapeData(hex2bin($reply));
        $header = substr($rawData, 0, 2);
        $crc = substr($rawData, 2, 1);
        $command = substr($rawData, 3, 1);
        $data = substr($rawData, 4, strlen($rawData) - 6); //truncate header,crc, command and footer

        $expected_crc = $this->createChecksum($header . $command . $data);
        IPS_LogMessage("LWZ303", "CRC_EXPECTED:" . toHex($expected_crc));
        IPS_LogMessage("LWZ303", "CRC:" . toHex($crc));
        IPS_LogMessage("LWZ303", "COMMAND:" . toHex($command));
        IPS_LogMessage("LWZ303", "DATA:" . toHex($data));
        if ($expected_crc == $crc) {
            IPS_LogMessage("LWZ303", "COMMAND VALID");
            $this->parseCommandReply($command, $data);
        }
        else {
            IPS_LogMessage("LWZ303", "INVALID CRC");
            $this->resetCommunication();
        }

    }

    private function parseBuffer()
    {
        $buffer = GetValue($this->GetIDForIdent("buffer"));
        $state = GetValue($this->GetIDForIdent("state"));

        IPS_LogMessage("LWZ303", "Current STATE:" . $state);

        switch ($state) {

            //We are waiting for the ACK to be ready to send a new command
            case LWZ303::STATE_WAIT_ACK_COMMAND_INIT :

                if (substr($buffer, 0, 2) == LWZ303::command_ack) {
                    IPS_LogMessage("LWZ303", "INIT ACK RECEIVED");
                    $this->clearBuffer(2);
                    SetValue($this->GetIDForIdent("state"), LWZ303::STATE_WAIT_ACK_COMMAND);
                    $this->sendCommandInQueue();

                }
                break;

            //We requested to send a command
            case LWZ303::STATE_WAIT_ACK_COMMAND:
                if (substr($buffer, 0, 4) == LWZ303::command_ack . LWZ303::command_readytosend) {
                    IPS_LogMessage("LWZ303", "COMMAND ACK+READYTOSEND RECEIVED");
                    $this->clearBuffer(4);
                    SetValue($this->GetIDForIdent("state"), LWZ303::STATE_WAIT_HEADER);
                    $this->sendCommandtoParent(LWZ303::command_ack);
                }
                break;

            case LWZ303::STATE_WAIT_HEADER:
                switch (substr($buffer, 0, 4)) {
                    case LWZ303::response_header_get_ok:
                        IPS_LogMessage("LWZ303", "HEADER RECEIVED");

                        SetValue($this->GetIDForIdent("state"), LWZ303::STATE_WAIT_DATA);
                        $buffer = GetValue($this->GetIDForIdent("buffer"));
                        if ($buffer != "") {
                            /*There is remaining data, continue parsing..*/
                            $this->parseBuffer();
                            return;
                        }
                        break;

                    case LWZ303::response_header_error_crc:
                        IPS_LogMessage("LWZ303", "ERROR RECEIVED: Invalid CRC");
                        $this->resetCommunication();
                        break;

                    case LWZ303::response_header_error_unknown_cmd:
                        IPS_LogMessage("LWZ303", "ERROR RECEIVED: Unknown command");
                        $this->resetCommunication();
                        break;

                    case LWZ303::response_header_error_unknown_req:
                        IPS_LogMessage("LWZ303", "ERROR RECEIVED: Unknown request");
                        $this->resetCommunication();
                        break;
                }


                break;

            case LWZ303::STATE_WAIT_DATA:
                $current_response = GetValue($this->GetIDForIdent("response")) . $buffer;
                $this->clearBuffer(-1);
                SetValue($this->GetIDForIdent("response"), $current_response);
                if (substr($current_response, -4, 4) == LWZ303::response_footer) {
                    SetValue($this->GetIDForIdent("response"), "");
                    SetValue($this->GetIDForIdent("state"), LWZ303::STATE_WAIT_NONE);
                    $this->sendCommandtoParent(LWZ303::command_ack);
                    $this->parseReply($current_response);
                }


                break;

        }

    }


}

?>
