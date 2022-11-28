<?php


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::get('dev/accurate-customer-list', function (\App\Http\Requests\Request $request) {
    if ($request->get('token') != '0102030405') abort('RUNNING FAILED. CODE INVALID!');

    $response = collect();
    $a = 0; $max = 0;

    do {
        $res = \App\Models\Income\Customer::accurate()->api->list([
            'fields' => 'id,name,customerNo',
            'sp.pageSize' => 100,
            'sp.page' => $a+1
        ]);
        $max = $res['sp']['pageCount'] ?? 0;
        $response->push($res['d']);

        $a++;
    } while ($a < $max);

    return $response->collapse()->all();
});

Route::get('dev/accurate-customer-update', function (\App\Http\Requests\Request $request) {
    if ($request->get('token') != '0102030405') abort('RUNNING FAILED. CODE INVALID!');

    $success = collect();
    $failed = collect();

    $data = [
        ["code" => "C.00012","name" => "ARIES SOETARMAJI","accurate_model_id" => "50"],
        ["code" => "ASP","name" => "BAPAK ASEP","accurate_model_id" => "100"],
        ["code" => "JHN","name" => "BPK. JOHAN","accurate_model_id" => "101"],
        ["code" => "STO","name" => "BPK. SUTANTO","accurate_model_id" => "51"],
        ["code" => "TMY","name" => "BPK. TOMMY","accurate_model_id" => "102"],
        ["code" => "SUS","name" => "CV, SUKSES UTAMA STEEL","accurate_model_id" => "53"],
        ["code" => "BTP","name" => "CV. BENGAWAN TEKNIK PRATAMA","accurate_model_id" => "103"],
        ["code" => "MIK","name" => "CV. METALINDO INTI KEMILAU","accurate_model_id" => "104"],
        ["code" => "PMT","name" => "CV. PRIMATIO","accurate_model_id" => "105"],
        ["code" => "SEP","name" => "CV. SEMBADA ERA PERKASA","accurate_model_id" => "52"],
        ["code" => "C.00003","name" => "MADSANI","accurate_model_id" => "1800"],
        ["code" => "C.00014","name" => "MDL","accurate_model_id" => "54"],
        ["code" => "C.00011","name" => "NMI","accurate_model_id" => "55"],
        ["code" => "ECU","name" => "PT. EVERCONT UTAMA","accurate_model_id" => "153"],
        ["code" => "ACJ","name" => "PT. ADHI CHANDRA JAYA","accurate_model_id" => "106"],
        ["code" => "ADW","name" => "PT. ADHI WIJAYACITRA","accurate_model_id" => "107"],
        ["code" => "ASI","name" => "PT. ADYAWINSA STAMPING INDUSTRIES","accurate_model_id" => "108"],
        ["code" => "AMP","name" => "PT. AFTECH MULTINDO PERKASA","accurate_model_id" => "109"],
        ["code" => "AFI","name" => "PT. AICHI FORGING INDONESIA","accurate_model_id" => "110"],
        ["code" => "AIA","name" => "PT. AISIN INDONESIA","accurate_model_id" => "111"],
        ["code" => "AAI","name" => "PT. AKEBONO BRAKE ASTRA INDONESIA","accurate_model_id" => "112"],
        ["code" => "AMB","name" => "PT. ALPINDO MITRA BAJA","accurate_model_id" => "114"],
        ["code" => "AKC","name" => "PT. ANEKA KARYA CEMERLANG","accurate_model_id" => "115"],
        ["code" => "ADI","name" => "PT. ANUGERAH DAYA INDUSTRI KOMPONEN UTAMA","accurate_model_id" => "116"],
        ["code" => "AND","name" => "PT. ANUGRAH DIVINA","accurate_model_id" => "117"],
        ["code" => "AFP","name" => "PT. ARAPUTRA FORTUNA PERKASA","accurate_model_id" => "56"],
        ["code" => "AMI","name" => "PT. ASABA METAL INDUSTRI","accurate_model_id" => "118"],
        ["code" => "AMA","name" => "PT. ASALTA MANDIRI AGUNG","accurate_model_id" => "119"],
        ["code" => "AHI","name" => "PT. ASNO HORIE INDONESIA","accurate_model_id" => "120"],
        ["code" => "ATS","name" => "PT. ASTEER THAI SUMMIT","accurate_model_id" => "57"],
        ["code" => "ATI","name" => "PT. ATAKA TECHNOLOGY INDONESIA","accurate_model_id" => "121"],
        ["code" => "BAP","name" => "PT. BAKRIE AUTOPARTS","accurate_model_id" => "122"],
        ["code" => "BSI","name" => "PT. BELLSONICA INDONESIA","accurate_model_id" => "123"],
        ["code" => "BDK","name" => "PT. BERDIKARI METAL ENGINEERING","accurate_model_id" => "124"],
        ["code" => "BMI","name" => "PT. BINTANG MATRIX INDONESIA","accurate_model_id" => "125"],
        ["code" => "BMC","name" => "PT. BRAJA MUKTI CAKRA","accurate_model_id" => "126"],
        ["code" => "C.00016","name" => "PT. CAHAYA GLOBAL INFINITY PRATAMA","accurate_model_id" => "1303"],
        ["code" => "CHA","name" => "PT. CAHAYA HIDUP INDONESIA","accurate_model_id" => "1201"],
        ["code" => "CAC","name" => "PT. CAHYA ANUGRAH CEMERLANG","accurate_model_id" => "127"],
        ["code" => "CBP","name" => "PT. CAKRA BUANA PERKASA","accurate_model_id" => "128"],
        ["code" => "CCE","name" => "PT. CHANDRA NUGERAH CEMERLANG","accurate_model_id" => "129"],
        ["code" => "CCI","name" => "PT. CHANDRA NUGERAHCIPTA","accurate_model_id" => "130"],
        ["code" => "CHI","name" => "PT. CHIN HAUR INDONESIA","accurate_model_id" => "131"],
        ["code" => "CIP","name" => "PT. CIKARANG PRESISI","accurate_model_id" => "132"],
        ["code" => "CNI","name" => "PT. CIPTA NISSIN INDUSTRIES","accurate_model_id" => "134"],
        ["code" => "CPL","name" => "PT. CIPTA PERDANA LANCAR","accurate_model_id" => "135"],
        ["code" => "CSI","name" => "PT. CIPTA SAKSAMA INDONESIA","accurate_model_id" => "136"],
        ["code" => "CTP","name" => "PT. CIPTA TEKNINDO PRAMUDIRA","accurate_model_id" => "137"],
        ["code" => "CTA","name" => "PT. CIPTA TEKNINDO PRIMA","accurate_model_id" => "138"],
        ["code" => "CKU","name" => "PT. CIPTAJAYA KREASINDO UTAMA","accurate_model_id" => "133"],
        ["code" => "CNK","name" => "PT. CITRA NUGERAH KARYA","accurate_model_id" => "139"],
        ["code" => "CPM","name" => "PT. CITRA PLASTIK MAKMUR","accurate_model_id" => "140"],
        ["code" => "DBK","name" => "PT. DAE BAEK","accurate_model_id" => "141"],
        ["code" => "DWI","name" => "PT. DENKO WAHANA INDUSTRIES","accurate_model_id" => "142"],
        ["code" => "DNS","name" => "PT. DENSO INDONESIA","accurate_model_id" => "143"],
        ["code" => "DPM-1","name" => "PT. DHARMA POLIMETAL","accurate_model_id" => "144"],
        ["code" => "DPM","name" => "PT. DHARMA POLIMETAL Tbk","accurate_model_id" => "59"],
        ["code" => "DPP","name" => "PT. DHARMA PRECISION PARTS","accurate_model_id" => "145"],
        ["code" => "DRC","name" => "PT. DIAN RAYA CIPTA","accurate_model_id" => "146"],
        ["code" => "DAC","name" => "PT. DUTA MANDIRI ANUGERAH CEMERLANG","accurate_model_id" => "60"],
        ["code" => "DIK","name" => "PT. DWIDAYA INTI KREASI","accurate_model_id" => "147"],
        ["code" => "DKS","name" => "PT. DWIKARYA SEJAHTERA","accurate_model_id" => "148"],
        ["code" => "DUI","name" => "PT. DWIUTAMA INTITERANG","accurate_model_id" => "149"],
        ["code" => "ELI","name" => "PT. ELIM Tech","accurate_model_id" => "150"],
        ["code" => "EPC","name" => "PT. ESITAS PACIFIC","accurate_model_id" => "151"],
        ["code" => "ESK","name" => "PT. ESKA CAHYADI BERSAUDARA","accurate_model_id" => "152"],
        ["code" => "EXM","name" => "PT. EXIM & MFR INDONESIA","accurate_model_id" => "154"],
        ["code" => "FTI","name" => "PT. FANMAS TEKNOLOGI INDONESIA","accurate_model_id" => "155"],
        ["code" => "FTS","name" => "PT. FTS AUTOMOTIVE INDONESIA","accurate_model_id" => "61"],
        ["code" => "GGS","name" => "PT. GANZU GISMA SEIKO","accurate_model_id" => "156"],
        ["code" => "GMU","name" => "PT. GARUDA METAL UTAMA","accurate_model_id" => "63"],
        ["code" => "GMI","name" => "PT. GARUDA METALINDO Tbk.","accurate_model_id" => "62"],
        ["code" => "C.00002","name" => "PT. GARUDA METALINDO Tbk.","accurate_model_id" => "1350"],
        ["code" => "GKD","name" => "PT. GEMALA KEMPA DAYA","accurate_model_id" => "157"],
        ["code" => "GIP","name" => "PT. GINSA INTI PRATAMA","accurate_model_id" => "158"],
        ["code" => "GDM","name" => "PT. GLOBAL DIMENSI METALINDO","accurate_model_id" => "159"],
        ["code" => "C.00015","name" => "PT. GLOBAL TEKNIK PERKASA INDONESIA","accurate_model_id" => "64"],
        ["code" => "GSI","name" => "PT. GOKO SPRING INDONESIA","accurate_model_id" => "160"],
        ["code" => "GSS","name" => "PT. GUNA SENAPUTRA SEJAHTERA","accurate_model_id" => "161"],
        ["code" => "HKP","name" => "PT. H-ONE KOGI PRIMA AUTO TECHNOLOGIES INDONESIA","accurate_model_id" => "169"],
        ["code" => "HDK","name" => "PT. HADEKA PRIMANTARA","accurate_model_id" => "162"],
        ["code" => "HTI","name" => "PT. HAMATETSU INDONESIA","accurate_model_id" => "163"],
        ["code" => "HKB","name" => "PT. HANYA KARYA BAHANA","accurate_model_id" => "164"],
        ["code" => "HAI","name" => "PT. HIGH ACE INDUSTRIES","accurate_model_id" => "165"],
        ["code" => "HOF","name" => "PT. HOFZ INDONESIA","accurate_model_id" => "166"],
        ["code" => "HPM","name" => "PT. HONDA PROSPECT MOTOR","accurate_model_id" => "168"],
        ["code" => "ICP","name" => "PT. INDOCIPTA HASTA PERKASA","accurate_model_id" => "65"],
        ["code" => "IMS","name" => "PT. INDOMITRA SEDAYA","accurate_model_id" => "171"],
        ["code" => "ITS","name" => "PT. INDONESIA THAI SUMMIT AUTO","accurate_model_id" => "172"],
        ["code" => "C.00010","name" => "PT. INDORACK MULTIKREASI","accurate_model_id" => "66"],
        ["code" => "ISI","name" => "PT. INDOSAFETY SENTOSA INDUSTRY","accurate_model_id" => "173"],
        ["code" => "IMU","name" => "PT. INDOSEIKI METALUTAMA","accurate_model_id" => "174"],
        ["code" => "IPJ","name" => "PT. INDTA PRATAMA JAYA","accurate_model_id" => "176"],
        ["code" => "IMV","name" => "PT. INGRESS MALINDO VENTURES","accurate_model_id" => "177"],
        ["code" => "IGP","name" => "PT. INTI GANDA PERDANA","accurate_model_id" => "178"],
        ["code" => "ILU","name" => "PT. INTI LOGAM UTAMA","accurate_model_id" => "179"],
        ["code" => "IPM","name" => "PT. INTI POLYMETAL","accurate_model_id" => "180"],
        ["code" => "IAN","name" => "PT. IWASA AUTOPARTS NUSANTARA","accurate_model_id" => "67"],
        ["code" => "JIC","name" => "PT. JAYA INDAH CASTING","accurate_model_id" => "181"],
        ["code" => "JBE","name" => "PT. JUARA BIKE","accurate_model_id" => "182"],
        ["code" => "KZS","name" => "PT. KAIZEN PRESISI SUKSES","accurate_model_id" => "183"],
        ["code" => "KMI","name" => "PT. KAJI MACHINERY INDONESIA","accurate_model_id" => "3601"],
        ["code" => "KSI","name" => "PT. KANEMITSU SGS INDONESIA","accurate_model_id" => "184"],
        ["code" => "KBU","name" => "PT. KARYA BAHANA UNIGAM","accurate_model_id" => "185"],
        ["code" => "KYB","name" => "PT. KAYABA INDONESIA","accurate_model_id" => "186"],
        ["code" => "KSF","name" => "PT. KING STEEL FASTERNER","accurate_model_id" => "1602"],
        ["code" => "KEI","name" => "PT. KITADA ENGINEERING INDONESIA","accurate_model_id" => "187"],
        ["code" => "KYK","name" => "PT. KIYOKUNI INDONESIA","accurate_model_id" => "188"],
        ["code" => "KMD","name" => "PT. KOMODA INDONESIA","accurate_model_id" => "189"],
        ["code" => "KFN","name" => "PT. KOMPONEN FUTABA NUSAPERSADA","accurate_model_id" => "190"],
        ["code" => "KYW","name" => "PT. KYOWA INDONESIA","accurate_model_id" => "191"],
        ["code" => "LAJ","name" => "PT. LAISINDO ANUGERAH JAYAABADI","accurate_model_id" => "68"],
        ["code" => "LTP","name" => "PT. LESTARI TEKNIK PLASTIKATAMA","accurate_model_id" => "192"],
        ["code" => "LFI","name" => "PT. LNTAN FASTENER INDONESIA","accurate_model_id" => "69"],
        ["code" => "C.00005","name" => "PT. LOTUS INDAH UTAMA","accurate_model_id" => "70"],
        ["code" => "MWT","name" => "PT. MADA WIKRI TUNGGAL","accurate_model_id" => "193"],
        ["code" => "MSJ","name" => "PT. MAHAKARYA STAMPING JAYA","accurate_model_id" => "194"],
        ["code" => "C.00004","name" => "PT. MANGGALA JAYABAJA UTAMA","accurate_model_id" => "1850"],
        ["code" => "MHI","name" => "PT. MARUHACHI INDONESIA","accurate_model_id" => "195"],
        ["code" => "MAJ","name" => "PT. MEKAR ARMADA JAYA","accurate_model_id" => "196"],
        ["code" => "MCM","name" => "PT. MENARA CIPTA METALINDO","accurate_model_id" => "197"],
        ["code" => "MTM","name" => "PT. MENARA TERUS MAKMUR","accurate_model_id" => "198"],
        ["code" => "MPP","name" => "PT. MESINDO PUTRA PERKASA","accurate_model_id" => "199"],
        ["code" => "MCI","name" => "PT. METAL CORFIX INDONESIA","accurate_model_id" => "71"],
        ["code" => "MDR","name" => "PT. METAL DIAMETER","accurate_model_id" => "200"],
        ["code" => "MMM","name" => "PT. METALINDO MULTIDINAMIKA MANDIRI","accurate_model_id" => "201"],
        ["code" => "MES","name" => "PT. METINDO ERASAKTI","accurate_model_id" => "202"],
        ["code" => "MMP","name" => "PT. MITRAMETAL PERKASA","accurate_model_id" => "203"],
        ["code" => "MIP","name" => "PT. MITSUBA INDONESIA PIPE PARTS","accurate_model_id" => "204"],
        ["code" => "MSI","name" => "PT. MITSUTOYO INDONESIA","accurate_model_id" => "205"],
        ["code" => "MAM","name" => "PT. MIWA ASALTA MANUFACTURING","accurate_model_id" => "206"],
        ["code" => "MMW","name" => "PT. MIZUSHIMA METAL WORKS INDONESIA","accurate_model_id" => "207"],
        ["code" => "MLI","name" => "PT. MOON LION INDUSTRIES INDONESIA","accurate_model_id" => "208"],
        ["code" => "NIC","name" => "PT. NAMICOH INDONESIA COMPONENT","accurate_model_id" => "210"],
        ["code" => "NKP","name" => "PT. NANDYA KARYA PERKASA","accurate_model_id" => "211"],
        ["code" => "NMI","name" => "PT. NECCO MANUFACTURING INDONESIA","accurate_model_id" => "212"],
        ["code" => "NCI","name" => "PT. NIC INDONESIA","accurate_model_id" => "213"],
        ["code" => "NCE","name" => "PT. NIKKO CAHAYA ELECTRIC","accurate_model_id" => "3751"],
        ["code" => "NMW","name" => "PT. NIPPON METALWORK INDUSTRY","accurate_model_id" => "72"],
        ["code" => "NTP","name" => "PT. NIRMALA TIRTA PUTRA","accurate_model_id" => "214"],
        ["code" => "NII","name" => "PT. NISSHO INDUSTRY INDONESIA","accurate_model_id" => "216"],
        ["code" => "NIJ","name" => "PT. NUSA INDAH JAYA UTAMA","accurate_model_id" => "217"],
        ["code" => "NIM","name" => "PT. NUSA INDOMETAL MANDIRI","accurate_model_id" => "218"],
        ["code" => "NTC","name" => "PT. NUSA TOYOTETSU","accurate_model_id" => "219"],
        ["code" => "NTE","name" => "PT. NUSA TOYOTETSU II","accurate_model_id" => "220"],
        ["code" => "NMP","name" => "PT. Naga Mas Intipratama","accurate_model_id" => "209"],
        ["code" => "PTT","name" => "PT. PAMINDO TIGA T","accurate_model_id" => "221"],
        ["code" => "PSC","name" => "PT. PANACIPTA SEINAN COMPONENTS","accurate_model_id" => "222"],
        ["code" => "PGI","name" => "PT. PANASONIC GOBEL LIFE SOLUTIONS MFG INDONESIA","accurate_model_id" => "223"],
        ["code" => "PTC","name" => "PT. PATEC PRESISI ENGINEERING","accurate_model_id" => "224"],
        ["code" => "PMP","name" => "PT. PEMA META PRESINDO","accurate_model_id" => "73"],
        ["code" => "PMS","name" => "PT. PLATINDO MAKMUR SENTOSA","accurate_model_id" => "225"],
        ["code" => "PSP","name" => "PT. PRADANA SIRONA PERSADA","accurate_model_id" => "1251"],
        ["code" => "PMJ","name" => "PT. PRESS METALINDO JAYA","accurate_model_id" => "226"],
        ["code" => "PTI","name" => "PT. PROGRESS TOYO (INDONESIA)","accurate_model_id" => "227"],
        ["code" => "RPA","name" => "PT. RACHMAT PERDANA ADHIMETAL","accurate_model_id" => "229"],
        ["code" => "RFM","name" => "PT. RAJAWALI FASTENER MANUFACTURING","accurate_model_id" => "74"],
        ["code" => "RNF","name" => "PT. RODAMAS NUANSA FORTUNA","accurate_model_id" => "230"],
        ["code" => "SJI","name" => "PT. SAKURA JAVA INDONESIA","accurate_model_id" => "231"],
        ["code" => "SNH","name" => "PT. SANOH INDONESIA","accurate_model_id" => "232"],
        ["code" => "SUP","name" => "PT. SARANA UNGGUL PRATAMA","accurate_model_id" => "233"],
        ["code" => "STP","name" => "PT. SARI TAKAGI ELOK PRODUK (STEP)","accurate_model_id" => "234"],
        ["code" => "SJM","name" => "PT. SEBASTIAN JAYA METAL","accurate_model_id" => "235"],
        ["code" => "SDI","name" => "PT. SELARAS DONLIM INDONESIA","accurate_model_id" => "1551"],
        ["code" => "SMS","name" => "PT. SERAYU METALINDO STEEL","accurate_model_id" => "236"],
        ["code" => "SGS","name" => "PT. SETIA GUNA SEJATI","accurate_model_id" => "237"],
        ["code" => "SGM","name" => "PT. SGMW MOTOR INDONESIA","accurate_model_id" => "75"],
        ["code" => "SHI","name" => "PT. SHIN HEUNG INDONESIA","accurate_model_id" => "238"],
        ["code" => "SDM","name" => "PT. SIDO MAKMUR","accurate_model_id" => "800"],
        ["code" => "SJY","name" => "PT. SITONG JAYA INDONESIA","accurate_model_id" => "77"],
        ["code" => "SMI","name" => "PT. SMAP INDONESIA","accurate_model_id" => "240"],
        ["code" => "SMC","name" => "PT. SOMIC INDONESIA","accurate_model_id" => "241"],
        ["code" => "SORAYA","name" => "PT. SORAYA INTERINDO","accurate_model_id" => "242"],
        ["code" => "SMP","name" => "PT. STAR MUSTIKA PLASTMETAL","accurate_model_id" => "243"],
        ["code" => "SBM","name" => "PT. SUMBER BAHAGIA METALINDO","accurate_model_id" => "246"],
        ["code" => "SAI","name" => "PT. SUMMIT ADYAWINSA INDONESIA","accurate_model_id" => "247"],
        ["code" => "SII","name" => "PT. SUNCHIRIN INDUSTRIES INDONESIA","accurate_model_id" => "248"],
        ["code" => "SIS","name" => "PT. SUPLAINDO SEJAHTERA","accurate_model_id" => "249"],
        ["code" => "SKSL","name" => "PT. SURYA KENCANA SUKSES LESTARI","accurate_model_id" => "250"],
        ["code" => "SSY","name" => "PT. SURYA SHUENN YUEH INDUSTRY","accurate_model_id" => "251"],
        ["code" => "TDS","name" => "PT. THASIMA DAYA SENTOSA","accurate_model_id" => "252"],
        ["code" => "TBK","name" => "PT. TJOKRO BERSAUDARA KOMPONENINDO","accurate_model_id" => "253"],
        ["code" => "TSM","name" => "PT. TOSAMA ABADI","accurate_model_id" => "254"],
        ["code" => "TYI","name" => "PT. TOYOTOMO INDONESIA","accurate_model_id" => "255"],
        ["code" => "TMP","name" => "PT. TOZEN MECHANICAL PRODUCTS","accurate_model_id" => "256"],
        ["code" => "TDL","name" => "PT. TRI DAYA LANGGENG","accurate_model_id" => "78"],
        ["code" => "TJU","name" => "PT. TRIJAYA UNION","accurate_model_id" => "79"],
        ["code" => "TCH","name" => "PT. TRIMITRA CHITRAHASTA","accurate_model_id" => "257"],
        ["code" => "TKM","name" => "PT. TRIMITRA KARYA MANDIRI","accurate_model_id" => "258"],
        ["code" => "TRX","name" => "PT. TRIX INDONESIA","accurate_model_id" => "259"],
        ["code" => "THI","name" => "PT. TSUANG HINE INDUSTRIAL","accurate_model_id" => "260"],
        ["code" => "T&A","name" => "PT. TSUZUKI & ASAMA MANUFACTURING","accurate_model_id" => "261"],
        ["code" => "TIM","name" => "PT. TSUZUKI INDONESIA MANUFACTURING","accurate_model_id" => "262"],
        ["code" => "UMP","name" => "PT. UNITED METAL PRODUCT","accurate_model_id" => "263"],
        ["code" => "UBS","name" => "PT. USAHA BERSAMA SUKSES","accurate_model_id" => "264"],
        ["code" => "UII","name" => "PT. USUI INTERNATIONAL INDONESIA","accurate_model_id" => "265"],
        ["code" => "VME","name" => "PT. VANADIUM MODERN ELECTROPLATING","accurate_model_id" => "266"],
        ["code" => "VSM","name" => "PT. VANADIUM SUKSES MANDIRI","accurate_model_id" => "267"],
        ["code" => "WAN","name" => "PT. WHAIN","accurate_model_id" => "268"],
        ["code" => "WOO","name" => "PT. WOO IN","accurate_model_id" => "270"],
        ["code" => "YSP","name" => "PT. YAMANI SPRING INDONESIA","accurate_model_id" => "271"],
        ["code" => "YSI","name" => "PT. YI SHEN INDUSTRIAL","accurate_model_id" => "272"],
        ["code" => "YOS","name" => "PT. YONG SHIN INDONESIA","accurate_model_id" => "273"],
        ["code" => "YPI","name" => "PT. YOSKA PRIMA INTI","accurate_model_id" => "80"],
        ["code" => "ZII","name" => "PT. ZIEGLER INDONESIA","accurate_model_id" => "274"],
        ["code" => "ALD","name" => "PT.ALDA HENKO INTERNUSA","accurate_model_id" => "113"],
        ["code" => "CII","name" => "PT.CHIYODA INDUSTRY INDONESIA","accurate_model_id" => "58"],
        ["code" => "HOT","name" => "PT.HOFZ OTOMOTIF TEKNOLOGI","accurate_model_id" => "167"],
        ["code" => "IHH","name" => "PT.ICHANG HYDRAULIC HARDWARE INDONESIA","accurate_model_id" => "170"],
        ["code" => "IMN","name" => "PT.INDOTECH METAL NUSANTARA","accurate_model_id" => "175"],
        ["code" => "NSK","name" => "PT.NISAKA LOGAMINDO","accurate_model_id" => "215"],
        ["code" => "PAT","name" => "PT.PUJIASIH TEHNIKINDO","accurate_model_id" => "228"],
        ["code" => "SAP","name" => "PT.SINAR AGUNG PEMUDA","accurate_model_id" => "239"],
        ["code" => "STH","name" => "PT.STEPHALUX","accurate_model_id" => "245"],
        ["code" => "WME","name" => "PT.WIJAYAMAJU ELECTROINDO","accurate_model_id" => "269"],
        ["code" => "C.00001","name" => "PURWANTO GOZALI","accurate_model_id" => "1202"],
        ["code" => "C.00006","name" => "TOMMY STAVENNY","accurate_model_id" => "3750"],
    ];

    app('db')->beginTransaction();

    \App\Models\Income\Customer::whereNotNull('accurate_model_id')->update(['accurate_model_id' => null]);

    foreach ($data as $row) {
        if ($customer = \App\Models\Income\Customer::where('code', $row['code'])->first())
        {
            $customer->accurate_model_id = $row['accurate_model_id'];
            $customer->save();
            $success->push(array_merge($row, ['sync' => 'SUCCESS']));
        }
        else $failed->push(array_merge($row, ['sync' => 'FAILED']));
    }

    app('db')->commit();

    return response()->json(['success' => $success, 'failed' => $failed]);

});

Route::prefix('v1')->namespace('Api')->group(function () {
    Route::name('app')->get('app', function () {
        return response()->json(setting()->all());
    });

    Route::apiResource('commentables', 'Commentables')->only(['index']);
    Route::name('delivery-rutes')->get('delivery-rutes', 'Incomes\DeliveryCheckouts@rutes');
    Route::name('delivery-rutes.show')->get('delivery-rutes/{id}', 'Incomes\DeliveryCheckouts@rute');

    Route::prefix('landing')->name('landing.')->group(function () {
        Route::name('schedule-boards')->get('schedule-boards', 'Transports\ScheduleBoards@landing');
    });

    Route::name('login')->post('login', 'Auth\Authentication@login');
    Route::name('register')->post('register', 'Auth\Authentication@register');

    $noauth = request()->has('noauth') && env('APP_ENV', 'local') == 'local';
    Route::middleware(($noauth ? [] : ['auth:api']))->group(function () {
        Route::post('uploads/file', 'Uploads@storeFile');
        Route::delete('uploads/file', 'Uploads@destroyFile');
        // Route::post('uploads/exist', 'Uploads@existFile');

        Route::prefix('auth')->name('auth.')->group(function () {
            Route::middleware(['auth:api'])->group(function () {
                Route::name('user')->post('/', 'Auth\Authentication@user');
                Route::name('valid-token')->post('/valid-token', 'Auth\Authentication@validToken');
                Route::name('confirm-password')->post('/confirm-password', 'Auth\Authentication@confirmPassword');
                Route::name('change-password')->post('/change-password', 'Auth\Authentication@setChangePassword');
                Route::name('logout')->post('logout', 'Auth\Authentication@logout');
            });
            Route::apiResource('users', 'Auth\Users');
            Route::apiResource('roles', 'Auth\Roles');
            Route::apiResource('permissions', 'Auth\Permissions');
        });

        Route::prefix('setting')->name('setting.')->group(function () {
            Route::post('/{name}', 'Setting@set');
        });

        Route::prefix('common')->name('common.')->group(function () {
            Route::post('items/{id}/sample-validation', 'Common\Items@sampleValidation');
            Route::get('items/stockables', 'Common\Items@stockables');
            Route::get('items/delivery-cards', 'Common\Items@delivery_cards');
            Route::get('items/invoice-cards', 'Common\Items@invoice_cards');
            Route::apiResource('items', 'Common\Items');
            Route::apiResource('employees', 'Common\Employees');
            Route::apiResource('rutes', 'Common\Rutes');
            Route::apiResource('category-item-prices', 'Common\CategoryItemPrices');
        });

        Route::prefix('incomes')->name('incomes.')->group(function () {
            Route::get('delivery-orders/items', 'Incomes\DeliveryOrders@items');

            Route::put('delivery-loads/{id}/restore', 'Incomes\DeliveryLoads@restore');
            Route::put('delivery-loads/{id}/print-log', 'Incomes\DeliveryLoads@setPrintLog');
            Route::put('delivery-loads/{id}/save-vehicle', 'Incomes\DeliveryLoads@vehicleUpdated');
            Route::post('customers/{id}/accurate/push', 'Incomes\Customers@push');
            Route::post('invoices/{id}/confirmed', 'Incomes\AccInvoices@confirmed');
            Route::post('invoices/{id}/reopened', 'Incomes\AccInvoices@reopened');
            Route::post('invoices/{id}/syncronized', 'Incomes\AccInvoices@syncronized');
            Route::get('request-order-items', 'Incomes\RequestOrders@items');
            Route::put('request-order-items/{id}/lock', 'Incomes\RequestOrders@setLockDetail');
            Route::put('request-order-items/{id}/unlock', 'Incomes\RequestOrders@setUnlockDetail');

            Route::post('delivery-orders/multi-validation', 'Incomes\DeliveryOrders@multiValidation');
            Route::put('delivery-orders/{delivery_order}/validation', 'Incomes\DeliveryOrders@validation');
            Route::put('delivery-orders/{delivery_order}/confirmation', 'Incomes\DeliveryOrders@confirmation');
            Route::put('delivery-orders/{delivery_order}/reconfirmation', 'Incomes\DeliveryOrders@reconfirmation');
            Route::put('delivery-orders/{delivery_order}/reopen', 'Incomes\DeliveryOrders@reopen');
            Route::put('delivery-orders/{delivery_order}/multi-revision', 'Incomes\DeliveryOrders@multiRevision');
            Route::put('delivery-orders/{delivery_order}/revision', 'Incomes\DeliveryOrders@revision');
            Route::put('delivery-orders/{delivery_order}/item-encasement', 'Incomes\DeliveryOrders@encasementItem');

            Route::apiResource('customers', 'Incomes\Customers');
            Route::apiResource('forecasts', 'Incomes\Forecasts');
            Route::apiResource('forecast-periods', 'Incomes\ForecastPeriods');
            Route::apiResource('forecast-loads', 'Incomes\ForecastLoads');
            Route::apiResource('request-orders', 'Incomes\RequestOrders');
            Route::apiResource('invoices', 'Incomes\AccInvoices');
            Route::apiResource('delivery-orders', 'Incomes\DeliveryOrders');
            Route::apiResource('delivery-tasks', 'Incomes\DeliveryTasks');
            Route::apiResource('delivery-loads', 'Incomes\DeliveryLoads');
            Route::apiResource('delivery-verifies', 'Incomes\DeliveryVerifies');
            Route::apiResource('delivery-checkouts', 'Incomes\DeliveryCheckouts');
            Route::apiResource('delivery-handovers', 'Incomes\DeliveryHandovers');


            Route::get('delivery-verifies/{id}/detail', 'Incomes\DeliveryVerifies@detail');
            Route::delete('delivery-verifies/{id}/detail', 'Incomes\DeliveryVerifies@destroyDetail');

            Route::prefix('delivery-order-internals')->group(function () {
                Route::post('', 'Incomes\DeliveryOrders@storeInternal');
                Route::put('{delivery_order}/revision', 'Incomes\DeliveryOrders@revisonInternal');
                Route::put('{delivery_order}/confirmed', 'Incomes\DeliveryOrders@confirmation');
                Route::get('{delivery_order}', 'Incomes\DeliveryOrders@show');
                Route::delete('{delivery_order}', 'Incomes\DeliveryOrders@destroy');
            });
        });

        Route::prefix('warehouses')->name('warehouses.')->group(function () {

            Route::get('incoming-goods/items', 'Warehouses\IncomingGoods@items');

            Route::get('incoming-goods/{incomingGoodId}/partial-validations', 'Warehouses\IncomingGoods@validations');
            Route::post('incoming-goods/{incomingGoodId}/partial-validations', 'Warehouses\IncomingGoods@storePartialValidation');
            Route::delete('incoming-goods/{incomingGoodId}/partial-validations/{id}', 'Warehouses\IncomingGoods@destroyPartialValidation');

            Route::put('incoming-goods/{incoming_good}/rejection', 'Warehouses\IncomingGoods@rejection');
            Route::put('incoming-goods/{incoming_good}/restoration', 'Warehouses\IncomingGoods@restoration');
            Route::put('incoming-goods/{incoming_good}/validation', 'Warehouses\IncomingGoods@validation');
            Route::put('incoming-goods/{incoming_good}/standardization', 'Warehouses\IncomingGoods@standardization');
            Route::put('incoming-goods/{incoming_good}/revision', 'Warehouses\IncomingGoods@revision');

            Route::put('deportation-goods/{deportation_good}/validation', 'Warehouses\DeportationGoods@validation');
            Route::put('deportation-goods/{deportation_good}/rejection', 'Warehouses\DeportationGoods@rejection');

            Route::apiResource('transports', 'Warehouses\Transports');
            Route::apiResource('incoming-goods', 'Warehouses\IncomingGoods');
            Route::apiResource('opnames', 'Warehouses\Opnames');
            Route::apiResource('opname-stocks', 'Warehouses\OpnameStocks');
            Route::apiResource('opname-vouchers', 'Warehouses\OpnameVouchers');
            Route::apiResource('deportation-goods', 'Warehouses\DeportationGoods');
        });

        Route::prefix('factories')->name('factories.')->group(function () {
            Route::get('work-orders/items', 'Factories\WorkOrders@items');
            Route::get('work-orders/lines', 'Factories\WorkOrders@lines');
            Route::get('work-orders/packings', 'Factories\WorkOrders@packings');
            Route::get('work-orders/hanger-lines', 'Factories\WorkOrders@hangerLines');

            Route::apiResource('work-productions', 'Factories\WorkProductions');
            Route::apiResource('work-orders', 'Factories\WorkOrders');
            Route::apiResource('packings', 'Factories\Packings');
            Route::apiResource('packing-loads', 'Factories\PackingLoads');
        });

        Route::prefix('transports')->name('transports.')->group(function () {
            Route::apiResource('schedule-boards', 'Transports\ScheduleBoards');
            Route::apiResource('trip-boards', 'Transports\Tripboards');
        });

        Route::prefix('references')->name('references.')->group(function () {
            Route::apiResource('departments', 'References\Departments');
            Route::apiResource('positions', 'References\Positions');
            Route::apiResource('vehicles', 'References\Vehicles');
            Route::apiResource('faults', 'References\Faults');
            Route::apiResource('type-faults', 'References\TypeFaults');
            Route::apiResource('lines', 'References\Lines');
            Route::apiResource('packareas', 'References\Packareas');
            Route::apiResource('shifts', 'References\Shifts');
            Route::apiResource('provinces', 'References\Provinces');
            Route::apiResource('units', 'References\Units');
            Route::apiResource('sizes', 'References\Sizes');
            Route::apiResource('brands', 'References\Brands');
            Route::apiResource('colors', 'References\Colors');
            Route::apiResource('reasons', 'References\Reasons');
            Route::apiResource('type-items', 'References\TypeItems');
            Route::apiResource('category-items', 'References\CategoryItems');
            Route::apiResource('specifications', 'References\Specifications');
        });
    });
});

$api = app('Dingo\Api\Routing\Router');
$api->version('v1', function ($api) {
    $api->GET('/', function () {
        return response()->json([
            'app' => env('APP_NAME'),
            'prefix' => env('API_PREFIX'),
            'version' => env('API_VERSION'),
        ]);
    });
    $api->group(['namespace' => 'App\Api\Controllers'], function ($api) {
        ## Guest Access Route
        $api->group(['middleware' => 'api'], function ($api) {
            $api->group(['prefix' => 'auth'], function ($api) {
                $api->post('/login', 'Auth\Login@store');
                // $api->post("register", 'Auth\RegisterController@register');
                // $api->get("register/{token}", 'Auth\RegisterController@registerActivate');
                // $api->post("password/email", 'Auth\PasswordResetController@createToken');
                // $api->get("password/reset/{token}", 'Auth\PasswordResetController@findToken');
                // $api->post("password/reset", 'Auth\PasswordResetController@reset');
            });
        });

        $api->resource('opname-vouchers', 'Warehouses\OpnameVouchers');

        ## User Access Route
        $api->group(['middleware' => 'auth:api'], function ($api) {
            $api->get('profile', 'Auth\Profile@show');
            ## Auth Routes
            $api->group(['prefix' => 'auth'], function ($api) {
                $api->get('logout', 'Auth\Login@logout');
            });
            ## Common Routes
            $api->group(['prefix' => 'common'], function ($api) {
                $api->resource('items', 'Common\Items');
            });
            ## Warehouse Routes
            $api->group(['prefix' => 'warehouses'], function ($api) {
                $api->resource('opname-vouchers', 'Warehouses\OpnameVouchers');
            });
        });
    });
});
