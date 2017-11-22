<?php

require_once(XOOPS_ROOT_PATH . '/modules/uhq_radiobasic/include/functions.php');

function b_uhqradiobasic_status_show($options)
{
    $block = array();

    // Set path & authentication for grabbing XML.
    if ($options[2] == "I") {
        $xmlpath = "/admin/stats.xml";
        $auth    = base64_encode($options[7] . ":" . $options[3]);
    } elseif ($options[2] == "S") {
        $xmlpath = "/admin.cgi?mode=viewxml";
        $auth    = base64_encode("admin:" . $options[3]);
    } else {
        $block['status']    = _MB_UHQRADIOBASIC_OFFAIR;
        $block['statusimg'] = _MB_UHQRADIOBASIC_OFFAIR_IMG;
        if ($options[19]) {
            $block['error']     = 1;
            $block['errorcode'] = _MB_UHQRADIOBASIC_ERROR_UNSUP_S . $options[2] . ".";
        }

        return $block;
    }

    // Get XML and make sure we get no errors.
    $xmldata = '';
    $errno   = uhqradiobasic_fetchxml($options[0], $options[1], $xmlpath, $auth, $xmldata);

    if ($errno) {
        $block['status']    = _MB_UHQRADIOBASIC_OFFAIR;
        $block['statusimg'] = _MB_UHQRADIOBASIC_OFFAIR_IMG;
        if ($options[19]) {
            $block['error']     = 1;
            $block['errorcode'] = _MB_UHQRADIOBASIC_ERROR_CONN . $errno;
        }

        return $block;
    }

    // Parse results for status & artist.
    if ($options[2] == "I") {

        // Determine if the mounts are available
        if (strpos($xmldata, $options[4])) {
            $mount = $options[4];
        } elseif ($options[5] == 1) {
            if (strpos($xmldata, $options[6])) {
                $mount = $options[6];
            } else {
                $block['status']    = _MB_UHQRADIOBASIC_OFFAIR;
                $block['statusimg'] = _MB_UHQRADIOBASIC_OFFAIR_IMG;
                if ($options[19]) {
                    $block['statusdetail'] = _MB_UHQRADIOBASIC_ERROR_FBNF;
                }

                return $block;
            }
        } else {
            $block['status']    = _MB_UHQRADIOBASIC_OFFAIR;
            $block['statusimg'] = _MB_UHQRADIOBASIC_OFFAIR_IMG;
            if ($options[19]) {
                $block['statusdetail'] = _MB_UHQRADIOBASIC_ERROR_MNF;
            }

            return $block;
        }

        // Extract Mount
        $xmlmount = uhqradiobasic_isolatexml($xmldata, $mount . '">', "</source>");

        // Process artist & title

        if (strpos($xmlmount, '<artist>') !== false) {
            // If the <artist> tag exists, this is easy.
            $block['artist'] = uhqradiobasic_isolatexml($xmlmount, '<artist>', '</artist>');
            $block['title']  = uhqradiobasic_isolatexml($xmlmount, '<title>', '</title>');
        } else {
            // If the <artist> tag is missing, make sure we have our delimiter
            if (strpos(uhqradiobasic_isolatexml($xmlmount, '<title>', '</title>'), ' - ')) {
                uhqradiobasic_titlesplit(
                    uhqradiobasic_isolatexml($xmlmount, '<title>', '</title>'),
                    $block['artist'],
                    $block['title']
                );
            } else {
                // Otherwise, just do the title only.
                $block['title'] = uhqradiobasic_isolatexml($xmlmount, '<title>', '</title>');
            }
        }

        // Extract Listener Count
        $block['count'] = uhqradiobasic_isolatexml($xmlmount, '<listeners>', '</listeners>');
    } elseif ($options[2] == "S") {

        // Extract Status
        $status = uhqradiobasic_isolatexml($xmldata, '<STREAMSTATUS>', '</STREAMSTATUS>');

        if ($status == 1) {
            // Extract Title & Split
            uhqradiobasic_titlesplit(
                uhqradiobasic_isolatexml($xmlmount, '<SONGTITLE>', '</SONGTITLE>'),
                $block['artist'],
                $block['title']
            );
            // Extract Listener Count
            $block['count'] = uhqradiobasic_isolatexml($xmlmount, '<CURRENTLISTENERS>', '</CURRENTLISTENERS>');
        } else {
            $block['status']    = _MB_UHQRADIOBASIC_OFFAIR;
            $block['statusimg'] = _MB_UHQRADIOBASIC_OFFAIR_IMG;
            if ($options[19]) {
                $block['statusdetail'] = _MB_UHQRADIOBASIC_ERROR_SU;
            }

            return $block;
        }
    }

    // If we get this far, we're good on our song info.  Process show names if we use them.

    if ($options[8] == 1) {

        // Get our field.
        if ($options[2] == "I") {
            $showname = uhqradiobasic_isolatexml($xmlmount, '<server_description>', '</server_description>');
        } elseif ($options[2] == "S") {
            $showname = uhqradiobasic_isolatexml($xmldata, '<SERVERTITLE>', '</SERVERTITLE>');
        }

        // Process Start Delimiter
        if ($options[9] == 1) {
            $spos     = strpos($showname, $options[10]);
            $showname = substr($showname, ($spos + strlen($options[10])));
        }

        // Process End Delimiter
        if ($options[11] == 1) {
            $epos     = strpos($showname, $options[12]);
            $showname = substr($showname, 0, $epos);
        }

        // Trim whitespace
        $block['statusdetail'] = trim($showname);
    }

    // Let's do an on-air link if we use it.

    if ($options[13] == 1) {
        $block['linkurl'] = $options[14];
        $block['target']  = $options[15];

        if ($block['target'] == "pop") {
            $block['popw'] = $options[16];
            $block['poph'] = $options[17];
        }
    }

    // Provide listener response if configured.

    if ($options[18] == 1) {
        if ($block['count'] == 0) {
            $block['listeners'] = _MB_UHQRADIOBASIC_LISTENERS_NONE;
        } elseif ($block['count'] == 1
        ) {
            $block['listeners'] = _MB_UHQRADIOBASIC_LISTENERS_ONE;
        } elseif ($block['count'] > 1) {
            $block['listeners'] = _MB_UHQRADIOBASIC_LISTENERS_MANY . $block['count'];
        }
    }

    // If we've gotten this far, we are on the air.

    $block['status']    = _MB_UHQRADIOBASIC_ONAIR;
    $block['statusimg'] = _MB_UHQRADIOBASIC_ONAIR_IMG;

    return $block;
}

function b_uhqradiobasic_status_edit($options)
{
    // Server IP
    $form = _MB_UHQRADIOBASIC_STATUS_OPTA;
    $form .= "<input type='text' name='options[0]' value='" . $options[0] . "' />";
    $form .= "<br />";

    // Server Port
    $form .= _MB_UHQRADIOBASIC_STATUS_OPTB;
    $form .= "<input type='text' name='options[1]' value='" . $options[1] . "' />";
    $form .= "<br />";

    // Server Type
    $form .= _MB_UHQRADIOBASIC_STATUS_OPTC;
    $form .= "<input type='radio' name='options[2]' value= 'I' ";
    if ($options[2] == "I") {
        $form .= "checked";
    }
    $form .= "/>";
    $form .= _MB_UHQRADIOBASIC_SERVER_I;
    $form .= "<input type='radio' name='options[2]' value= 'S' ";
    if ($options[2] == "S") {
        $form .= "checked";
    }
    $form .= "/>";
    $form .= _MB_UHQRADIOBASIC_SERVER_S;
    $form .= "<br />";

    // Stats PW
    $form .= _MB_UHQRADIOBASIC_STATUS_OPTD;
    $form .= "<input type='text' name='options[3]' value='" . $options[3] . "' />";
    $form .= "<br />";

    $form .= _MB_UHQRADIOBASIC_STATUS_ICECASTHDR;

    // Icecast Mount
    $form .= _MB_UHQRADIOBASIC_STATUS_OPTE;
    $form .= "<input type='text' name='options[4]' value='" . $options[4] . "' />";
    $form .= "<br />";

    // Use Fallback?
    $form .= _MB_UHQRADIOBASIC_STATUS_OPTG;
    $form .= "<input type='radio' name='options[5]' value= '1' ";
    if ($options[5] == "1") {
        $form .= "checked";
    }
    $form .= "/>";
    $form .= _MB_UHQRADIOBASIC_YES;
    $form .= "<input type='radio' name='options[5]' value= '0' ";
    if ($options[5] == "0") {
        $form .= "checked";
    }
    $form .= "/>";
    $form .= _MB_UHQRADIOBASIC_NO;
    $form .= "<br />";

    // Fallback Mount
    $form .= _MB_UHQRADIOBASIC_STATUS_OPTH;
    $form .= "<input type='text' name='options[6]' value='" . $options[6] . "' />";
    $form .= "<br />";

    // Icecast Admin UN
    $form .= _MB_UHQRADIOBASIC_STATUS_OPTI;
    $form .= "<input type='text' name='options[7]' value='" . $options[7] . "' />";
    $form .= "<br />";

    $form .= _MB_UHQRADIOBASIC_STATUS_SHOWHDR;

    // Use show names?

    $form .= _MB_UHQRADIOBASIC_STATUS_OPTJ;
    $form .= "<input type='radio' name='options[8]' value= '1' ";
    if ($options[8] == "1") {
        $form .= "checked";
    }
    $form .= "/>";
    $form .= _MB_UHQRADIOBASIC_YES;
    $form .= "<input type='radio' name='options[8]' value= '0' ";
    if ($options[8] == "0") {
        $form .= "checked";
    }
    $form .= "/>";
    $form .= _MB_UHQRADIOBASIC_NO;
    $form .= "<br />";

    // Use start delimiter?  If not, SOL.
    $form .= _MB_UHQRADIOBASIC_STATUS_OPTK;
    $form .= "<input type='radio' name='options[9]' value= '0' ";
    if ($options[9] == "0") {
        $form .= "checked";
    }
    $form .= "/>";
    $form .= _MB_UHQRADIOBASIC_SOL;
    $form .= "<input type='radio' name='options[9]' value= '1' ";
    if ($options[9] == "1") {
        $form .= "checked";
    }
    $form .= "/>";
    $form .= _MB_UHQRADIOBASIC_STATUS_OPTL;
    $form .= "<input type='text' name='options[10]' value='" . $options[10] . "' />";
    $form .= "<br />";

    // Use end delimiter?  If not, EOL.
    $form .= _MB_UHQRADIOBASIC_STATUS_OPTM;
    $form .= "<input type='radio' name='options[11]' value= '0' ";
    if ($options[11] == "0") {
        $form .= "checked";
    }
    $form .= "/>";
    $form .= _MB_UHQRADIOBASIC_EOL;
    $form .= "<input type='radio' name='options[11]' value= '1' ";
    if ($options[11] == "1") {
        $form .= "checked";
    }
    $form .= "/>";
    $form .= _MB_UHQRADIOBASIC_STATUS_OPTN;
    $form .= "<input type='text' name='options[12]' value='" . $options[12] . "' />";
    $form .= "<br />";

    $form .= _MB_UHQRADIOBASIC_STATUS_LINKHDR;

    // Use a tune-in link?
    $form .= _MB_UHQRADIOBASIC_STATUS_OPTO;
    $form .= "<input type='radio' name='options[13]' value= '1' ";
    if ($options[13] == "1") {
        $form .= "checked";
    }
    $form .= "/>";
    $form .= _MB_UHQRADIOBASIC_YES;
    $form .= "<input type='radio' name='options[13]' value= '0' ";
    if ($options[13] == "0") {
        $form .= "checked";
    }
    $form .= "/>";
    $form .= _MB_UHQRADIOBASIC_NO;
    $form .= "<br />";

    // Tune-In URL
    $form .= _MB_UHQRADIOBASIC_STATUS_OPTP;
    $form .= "<input type='text' name='options[14]' value='" . $options[14] . "' />";
    $form .= "<br />";

    // Tune-In Target
    $form .= _MB_UHQRADIOBASIC_STATUS_OPTQ;
    $form .= "<select name='options[15]'>";
    $form .= "<option value='_top' ";
    if ($options[15] == "_top") {
        $form .= "selected";
    }
    $form .= ">" . _MB_UHQRADIOBASIC_TARGET_SELF . "</option>";
    $form .= "<option value='_blank' ";
    if ($options[15] == "_blank") {
        $form .= "selected";
    }
    $form .= ">" . _MB_UHQRADIOBASIC_TARGET_NEW . "</option>";
    $form .= "<option value='pop' ";
    if ($options[15] == "pop") {
        $form .= "selected";
    }
    $form .= ">" . _MB_UHQRADIOBASIC_TARGET_POPUP . "</option>";
    $form .= "</select>";
    $form .= "<br />";

    // Pop-Up Dimensions
    $form .= _MB_UHQRADIOBASIC_STATUS_OPTRS;
    $form .= _MB_UHQRADIOBASIC_WIDTH;
    $form .= "<input type='text' name='options[16]' maxlength=3 value='" . $options[16] . "' />";
    $form .= _MB_UHQRADIOBASIC_HEIGHT;
    $form .= "<input type='text' name='options[17]' maxlength=3 value='" . $options[17] . "' />";
    $form .= "<br />";

    $form .= _MB_UHQRADIOBASIC_STATUS_OPTIONHDR;

    // Show listeners?
    $form .= _MB_UHQRADIOBASIC_STATUS_OPTT;
    $form .= "<input type='radio' name='options[18]' value= '1' ";
    if ($options[18] == "1") {
        $form .= "checked";
    }
    $form .= "/>";
    $form .= _MB_UHQRADIOBASIC_YES;
    $form .= "<input type='radio' name='options[18]' value= '0' ";
    if ($options[18] == "0") {
        $form .= "checked";
    }
    $form .= "/>";
    $form .= _MB_UHQRADIOBASIC_NO;
    $form .= "<br />";

    // Show Errors?
    $form .= _MB_UHQRADIOBASIC_STATUS_OPTU;
    $form .= "<input type='radio' name='options[19]' value= '1' ";
    if ($options[19] == "1") {
        $form .= "checked";
    }
    $form .= "/>";
    $form .= _MB_UHQRADIOBASIC_YES;
    $form .= "<input type='radio' name='options[19]' value= '0' ";
    if ($options[19] == "0") {
        $form .= "checked";
    }
    $form .= "/>";
    $form .= _MB_UHQRADIOBASIC_NO;
    $form .= "<br />";

    return $form;
}
