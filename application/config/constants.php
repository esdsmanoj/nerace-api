<?php
defined("BASEPATH") or exit("No direct script access allowed");
/*
|--------------------------------------------------------------------------
| Display Debug backtrace
|--------------------------------------------------------------------------
|
| If set to TRUE, a backtrace will be displayed along with php errors. If
| error_reporting is disabled, the backtrace will not display, regardless
| of this setting
|
*/
defined("SHOW_DEBUG_BACKTRACE") or define("SHOW_DEBUG_BACKTRACE", true);
/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
defined("FILE_READ_MODE") or define("FILE_READ_MODE", 0644);
defined("FILE_WRITE_MODE") or define("FILE_WRITE_MODE", 0666);
defined("DIR_READ_MODE") or define("DIR_READ_MODE", 0755);
defined("DIR_WRITE_MODE") or define("DIR_WRITE_MODE", 0755);
/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/
defined("FOPEN_READ") or define("FOPEN_READ", "rb");
defined("FOPEN_READ_WRITE") or define("FOPEN_READ_WRITE", "r+b");
defined("FOPEN_WRITE_CREATE_DESTRUCTIVE") or
    define("FOPEN_WRITE_CREATE_DESTRUCTIVE", "wb"); // truncates existing file data, use with care
defined("FOPEN_READ_WRITE_CREATE_DESTRUCTIVE") or
    define("FOPEN_READ_WRITE_CREATE_DESTRUCTIVE", "w+b"); // truncates existing file data, use with care
defined("FOPEN_WRITE_CREATE") or define("FOPEN_WRITE_CREATE", "ab");
defined("FOPEN_READ_WRITE_CREATE") or define("FOPEN_READ_WRITE_CREATE", "a+b");
defined("FOPEN_WRITE_CREATE_STRICT") or
    define("FOPEN_WRITE_CREATE_STRICT", "xb");
defined("FOPEN_READ_WRITE_CREATE_STRICT") or
    define("FOPEN_READ_WRITE_CREATE_STRICT", "x+b");
/*
|--------------------------------------------------------------------------
| Exit Status Codes
|--------------------------------------------------------------------------
|
| Used to indicate the conditions under which the script is exit()ing.
| While there is no universal standard for error codes, there are some
| broad conventions.  Three such conventions are mentioned below, for
| those who wish to make use of them.  The CodeIgniter defaults were
| chosen for the least overlap with these conventions, while still
| leaving room for others to be defined in future versions and user
| applications.
|
| The three main conventions used for determining exit status codes
| are as follows:
|
|    Standard C/C++ Library (stdlibc):
|       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
|       (This link also contains other GNU-specific conventions)
|    BSD sysexits.h:
|       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
|    Bash scripting:
|       http://tldp.org/LDP/abs/html/exitcodes.html
|
*/
defined("EXIT_SUCCESS") or define("EXIT_SUCCESS", 0); // no errors
defined("EXIT_ERROR") or define("EXIT_ERROR", 1); // generic error
defined("EXIT_CONFIG") or define("EXIT_CONFIG", 3); // configuration error
defined("EXIT_UNKNOWN_FILE") or define("EXIT_UNKNOWN_FILE", 4); // file not found
defined("EXIT_UNKNOWN_CLASS") or define("EXIT_UNKNOWN_CLASS", 5); // unknown class
defined("EXIT_UNKNOWN_METHOD") or define("EXIT_UNKNOWN_METHOD", 6); // unknown class member
defined("EXIT_USER_INPUT") or define("EXIT_USER_INPUT", 7); // invalid user input
defined("EXIT_DATABASE") or define("EXIT_DATABASE", 8); // database error
defined("EXIT__AUTO_MIN") or define("EXIT__AUTO_MIN", 9); // lowest automatically-assigned error code
defined("EXIT__AUTO_MAX") or define("EXIT__AUTO_MAX", 125); // highest automatically-assigned error code
// Custom
defined("MAIN_FOLDER") or define("MAIN_FOLDER", "agri-ecosystem-uat");
defined("NC_AUTH_USER") or define("NC_AUTH_USER", "famrut");
defined("NC_AUTH_PASSWORD") or define("NC_AUTH_PASSWORD", "famrut@123#");
defined("NETCARROTS") or
    define("NETCARROTS", "http://famrutdemo.netcarrots.in/");
defined("API_SERVER_KEY") or
    define(
        "API_SERVER_KEY",
        ""
    );
defined("KEYID_TEST") or define("KEYID_TEST", "");
defined("KEYSECRET_TEST") or
    define("KEYSECRET_TEST", "");
defined("DISPLAYCURRENCY") or define("DISPLAYCURRENCY", "");
defined("KEYID_LIVE") or define("KEYID_LIVE", "");
defined("KEYSECRET_LIVE") or
    define("KEYSECRET_LIVE", "");

define("PAYTM_MERCHANT_KEY_TEST", ""); //Change this constant's value with Merchant key received from Paytm.
define("PAYTM_MERCHANT_MID_TEST", ""); //Change this constant's value with MID (Merchant ID) received from
define("PAYTM_MERCHANT_WEBSITE_TEST", ""); //Change this constant's value with Website name received
defined("SEASION") or define("SEASION", ["Kharif", "Late Kharif", "Rabi"]);
define("BASE_PATH_PORTAL", ""); 
define("PARTNER_URL", ""); 
defined("UPLOAD_ROOT_FOLDER") or
    define("UPLOAD_ROOT_FOLDER", "");
//Nedfi Project
$prod_cat = [
    [
        "id" => 1,
        "title" => "Raw Product",
        "map_key" => "raw_product",
        "days" => 7,
    ],
    [
        "id" => 2,
        "title" => "Upcoming Product",
        "map_key" => "pre_processed_product",
        "days" => 8,
    ],
    [
        "id" => 3,
        "title" => "Processed Product",
        "map_key" => "processed_product",
        "days" => 30,
    ],
];
$prod_unit = [
    [
        "id" => 1,
        "title" => "KG",
        "map_key" => "kg",
        "short_title" => "KG",
    ],
    [
        "id" => 2,
        "title" => "Tonn",
        "map_key" => "tonn",
        "short_title" => "T",
    ],
    [
        "id" => 3,
        "title" => "Quintal",
        "map_key" => "quintal",
        "short_title" => "Q",
    ],
];
$prod_payment = [
    [
        "id" => 30,
        "title" => 30,
    ],
    [
        "id" => 50,
        "title" => 50,
    ],
    [
        "id" => 100,
        "title" => 100,
    ],
    [
        "id" => "payment_after_delivery",
        "title" => "Payment after delivery",
    ],
];
$season_list = [
    [
        "id" => 1,
        "title" => "Kharif",
        "map_key" => "kharif",
    ],
    [
        "id" => 2,
        "title" => "Late Kharif",
        "map_key" => "late_kharif",
    ],
    [
        "id" => 3,
        "title" => "Rabi",
        "map_key" => "rabi",
    ],
];
// 'Pending','Rejected','Live','Sold','Completed', 'Expired', 'Draft'
$trade_status = [
    [
        "id" => 1,
        "title" => "Pending",
        "map_key" => "pending",
        "statusClass" => "pending-status",
    ],
    [
        "id" => 2,
        "title" => "Rejected",
        "map_key" => "rejected",
        "statusClass" => "rejected-status",
    ],
    [
        "id" => 3,
        "title" => "Live",
        "map_key" => "live",
        "statusClass" => "green-status",
    ],
    [
        "id" => 4,
        "title" => "Sold",
        "map_key" => "sold",
        "statusClass" => "sold-status",
    ],
    [
        "id" => 5,
        "title" => "Completed",
        "map_key" => "completed",
        "statusClass" => "blue-status",
    ],
    [
        "id" => 6,
        "title" => "Expired",
        "map_key" => "expired",
        "statusClass" => "expired-status",
    ],
    [
        "id" => 7,
        "title" => "Self Sold",
        "map_key" => "self-sold",
        "statusClass" => "sold-status",
    ],
    [
        "id" => 8,
        "title" => "Draft",
        "map_key" => "draft",
        "statusClass" => "pending-status",
    ],
    [
        "id" => 9,
        "title" => "Bid Locked",
        "map_key" => "bid_locked",
        "statusClass" => "bid-locked",
    ],
];
$upcominig_trade_status = [
    [
        "id" => 1,
        "title" => "Pending",
        "map_key" => "pending",
        "statusClass" => "pending-status",
    ],
    [
        "id" => 2,
        "title" => "Rejected",
        "map_key" => "rejected",
        "statusClass" => "rejected-status",
    ],
    [
        "id" => 3,
        "title" => "Live",
        "map_key" => "live",
        "statusClass" => "green-status",
    ],
    [
        "id" => 8,
        "title" => "Draft",
        "map_key" => "draft",
        "statusClass" => "pending-status",
    ],
];
defined("PROD_CAT") or define("PROD_CAT", $prod_cat);
defined("PROD_UNIT") or define("PROD_UNIT", $prod_unit);
defined("PROD_PAYMENT") or define("PROD_PAYMENT", $prod_payment);
defined("SEASON_LIST") or define("SEASON_LIST", $season_list);
defined("TRADE_STATUS_LIST") or define("TRADE_STATUS_LIST", $trade_status);
defined("UPCOMING_TRADE_STATUS_LIST") or
    define("UPCOMING_TRADE_STATUS_LIST", $upcominig_trade_status);
$step_list = [
    [
        "id" => 1,
        "title" => "Basic Info",
        "map_key" => "step1",
    ],
    [
        "id" => 2,
        "title" => "Business Info",
        "map_key" => "step2",
    ],
    [
        "id" => 3,
        "title" => "Ekyc Verification",
        "map_key" => "step3",
    ],
];
$short_step_list = [
    [
        "id" => 1,
        "title" => "Basic Info",
        "map_key" => "step1",
    ],
];
defined("STEP_LIST") or define("STEP_LIST", $step_list);
defined("SHORT_STEP_LIST") or define("SHORT_STEP_LIST", $short_step_list);
// $user_type = [
//     [
//         'id' => 1,
//         'title' => 'Individual',
//         'map_key' => 'individual',
//     ],
//     [
//         'id' => 2,
//         'title' => 'Business',
//         'map_key' => 'business',
//     ],
// ];
$headers = getallheaders();
if (isset($headers["lang"])) {
    if ($headers["lang"] == "hi") {
        $user_type = [
            [
                "id" => 1,
                "title" => "व्यक्तिगत",
                "map_key" => "individual",
            ],
            [
                "id" => 2,
                "title" => "व्यवसाय",
                "map_key" => "business",
            ],
        ];
    } elseif ($headers["lang"] == "mr") {
        $user_type = [
            [
                "id" => 1,
                "title" => "वैयक्तिक",
                "map_key" => "individual",
            ],
            [
                "id" => 2,
                "title" => "व्यवसाय",
                "map_key" => "business",
            ],
        ];
    } else {
        $user_type = [
            [
                "id" => 1,
                "title" => "Individual",
                "map_key" => "individual",
            ],
            [
                "id" => 2,
                "title" => "Business",
                "map_key" => "business",
            ],
        ];
    }
} else {
    $user_type = [
        [
            "id" => 1,
            "title" => "Individual",
            "map_key" => "individual",
        ],
        [
            "id" => 2,
            "title" => "Business",
            "map_key" => "business",
        ],
    ];
}
defined("USER_TYPE") or define("USER_TYPE", $user_type);
$business_type = [
    [
        "id" => 1,
        "title" => "FPC",
        "map_key" => "FPC",
    ],
    [
        "id" => 2,
        "title" => "FPO",
        "map_key" => "FPO",
    ],
    [
        "id" => 3,
        "title" => "Cooperative Society",
        "map_key" => "cooperative_society",
    ],
    [
        "id" => 4,
        "title" => "Public Limited",
        "map_key" => "public_limited",
    ],
    [
        "id" => 5,
        "title" => "LLP",
        "map_key" => "LLP",
    ],
    [
        "id" => 6,
        "title" => "Private Limited",
        "map_key" => "private_limited",
    ],
    [
        "id" => 7,
        "title" => "Proprietor",
        "map_key" => "proprietor",
    ],
    [
        "id" => 8,
        "title" => "Other",
        "map_key" => "other",
    ],
];
defined("BUSINESS_TYPE") or define("BUSINESS_TYPE", $business_type);
$prod_details = [
    [
        "id" => 1,
        "title" => "Product",
        "map_key" => "product",
    ],
    [
        "id" => 2,
        "title" => "Produce",
        "map_key" => "produce",
    ],
];
defined("PROD_DETAILS") or define("PROD_DETAILS", $prod_details);
$demand_type = [
    [
        "id" => 1,
        "title" => "Immediate",
        "map_key" => "immediate",
    ],
    [
        "id" => 2,
        "title" => "Future",
        "map_key" => "future",
    ],
];
defined("DEMAND_TYPE") or define("DEMAND_TYPE", $demand_type);
$client_type = [
    [
        "id" => 1,
        "title" => "Buyer",
        "map_key" => "buyer",
    ],
    [
        "id" => 2,
        "title" => "Seller",
        "map_key" => "seller",
    ],
];
defined("CLIENT_TYPE") or define("CLIENT_TYPE", $client_type);
// 'Bid Placed'
$buyer_trade_status = [
    [
        "id" => 1,
        "title" => "Bid",
        "map_key" => "bid_placed",
        "txt_color" => "gold",
        "statusClass" => "green-status",
    ],
    [
        "id" => 2,
        "title" => "Revoked",
        "map_key" => "revoke",
        "txt_color" => "red",
        "statusClass" => "rejected-status",
    ],
    [
        "id" => 3,
        "title" => "Cancel",
        "map_key" => "cancel",
        "txt_color" => "red",
        "statusClass" => "rejected-status",
    ],
];
defined("BUYER_TRADE_STATUS") or
    define("BUYER_TRADE_STATUS", $buyer_trade_status);
$seller_trade_status = [
    [
        "id" => 1,
        "title" => "Accept",
        "map_key" => "accept",
        "txt_color" => "green",
        "statusClass" => "sold-status",
    ],
    [
        "id" => 2,
        "title" => "Revoked",
        "map_key" => "revoke",
        "txt_color" => "orange",
        "statusClass" => "rejected-status",
    ],
    [
        "id" => 3,
        "title" => "Rejected",
        "map_key" => "reject",
        "txt_color" => "red",
        "statusClass" => "rejected-status",
    ],
    [
        "id" => 4,
        "title" => "Cancel",
        "map_key" => "cancel",
        "txt_color" => "red",
        "statusClass" => "rejected-status",
    ],
    [
        "id" => 5,
        "title" => "Completed",
        "map_key" => "completed",
        "txt_color" => "pink",
        "statusClass" => "blue-status",
    ],
    [
        "id" => 9,
        "title" => "Bid Locked",
        "map_key" => "bid_locked",
        "txt_color" => "bid-locked",
        "statusClass" => "bid-locked",
    ],
];
defined("SELLER_TRADE_STATUS") or
    define("SELLER_TRADE_STATUS", $seller_trade_status);
$incentive_status = [
    [
        "id" => 1,
        "title" => "Applied",
        "map_key" => "applied",
    ],
    [
        "id" => 2,
        "title" => "Redeemed",
        "map_key" => "redeemed",
    ],
];
defined("INCENTIVE_STATUS") or define("INCENTIVE_STATUS", $incentive_status);
$business_scheme = [
    [
        "id" => 1,
        "title" => "Rashtriya Krishi Vikas Yojana (RKVY)",
        "map_key" => "scheme_1",
    ],
    [
        "id" => 2,
        "title" => "ASPIRE",
        "map_key" => "scheme_2",
    ],
    [
        "id" => 3,
        "title" => "MUDRA Bank",
        "map_key" => "scheme_3",
    ],
    [
        "id" => 4,
        "title" => "SIP-EIT",
        "map_key" => "scheme_4",
    ],
    [
        "id" => 5,
        "title" => "CEFPPC",
        "map_key" => "scheme_5",
    ],
    [
        "id" => 6,
        "title" => "Other",
        "map_key" => "scheme_6",
    ],
];
defined("BUSINESS_SCHEME") or define("BUSINESS_SCHEME", $business_scheme);
$fpc_business_scheme = [
    [
        "id" => 1,
        "title" => "MOVCDNER",
        "map_key" => "fpc_scheme_1",
    ],
    [
        "id" => 2,
        "title" => "10,000 FPO",
        "map_key" => "fpc_scheme_2",
    ],
    [
        "id" => 3,
        "title" => "SFAC",
        "map_key" => "fpc_scheme_3",
    ],
    [
        "id" => 4,
        "title" => "APART",
        "map_key" => "fpc_scheme_4",
    ],
    [
        "id" => 5,
        "title" => "State Flagship Handholding Scheme of Mizoram",
        "map_key" => "fpc_scheme_5",
    ],
    [
        "id" => 6,
        "title" => "MIDH",
        "map_key" => "fpc_scheme_6",
    ],
    [
        "id" => 7,
        "title" => "Other",
        "map_key" => "fpc_scheme_7",
    ],
    [
        "id" => 8,
        "title" => "None",
        "map_key" => "fpc_scheme_8",
    ],
];
defined("FPC_BUSINESS_SCHEME") or
    define("FPC_BUSINESS_SCHEME", $fpc_business_scheme);
define("API_BASE_PATH", ""); 
// 'Pending','Rejected','Sold','Completed', 'Expired', 'Bid Place'
$trade_bid_status = [
    [
        "id" => 1,
        "title" => "Bid",
        "map_key" => "bid_place",
        "statusClass" => "green-status",
    ],
    [
        "id" => 2,
        "title" => "Rejected",
        "map_key" => "rejected",
        "statusClass" => "rejected-status",
    ],
    [
        "id" => 4,
        "title" => "Sold",
        "map_key" => "sold",
        "statusClass" => "sold-status",
    ],
    [
        "id" => 5,
        "title" => "Completed",
        "map_key" => "completed",
        "statusClass" => "blue-status",
    ],
    [
        "id" => 6,
        "title" => "Expired",
        "map_key" => "expired",
        "statusClass" => "pink",
    ],
    [
        "id" => 9,
        "title" => "Bid Locked",
        "map_key" => "bid_locked",
        "statusClass" => "bid-locked",
    ],
];
defined("BID_STATUS") or define("BID_STATUS", $trade_bid_status);
// 'Bid Placed'
$buyer_trade_status_filter = [
    [
        "id" => 1,
        "title" => "Bid Placed",
        "map_key" => "bid_placed",
        "txt_color" => "gold",
        "statusClass" => "green-status",
    ],
    [
        "id" => 2,
        "title" => "Bid Revoked",
        "map_key" => "revoke",
        "txt_color" => "red",
        "statusClass" => "rejected-status",
    ],
    [
        "id" => 3,
        "title" => "Bid Rejected",
        "map_key" => "reject",
        "txt_color" => "red",
        "statusClass" => "rejected-status",
    ],
    [
        "id" => 4,
        "title" => "Sold",
        "map_key" => "sold",
        "txt_color" => "sold",
        "statusClass" => "sold-status",
    ],
    [
        "id" => 5,
        "title" => "Completed",
        "map_key" => "complete",
        "txt_color" => "Complete",
        "statusClass" => "blue-status",
    ],
    [
        "id" => 6,
        "title" => "Expired",
        "map_key" => "expired",
        "txt_color" => "Expired",
        "statusClass" => "expired-status",
    ],
    [
        "id" => 9,
        "title" => "Bid Locked",
        "map_key" => "bid_locked",
        "txt_color" => "bid-locked",
        "statusClass" => "bid-locked",
    ],
];
defined("BUYER_TRADE_STATUS_FILTER") or
    define("BUYER_TRADE_STATUS_FILTER", $buyer_trade_status_filter);
defined("CODE") or define("CODE", "nerace");
defined("SKIP_CODE") or define("SKIP_CODE", "1");
