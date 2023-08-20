<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InstitutionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                "id" => 1,
                "institution_name" => "UNMEB",
                "short_name" => "UNMEB",
                "institution_location" => "Kampala",
                "institution_type" => "Board",
                "code" => "1",
                "phone_no" => 234,
                "box_no" => 567
            ],
            [
                "id" => 2,
                "institution_name" => "ALICE ANUME MEMORIAL SCHOOL OF NURSING AND MIDWIFERY",
                "short_name" => "ALICE ANUME",
                "institution_location" => "PALLISA",
                "institution_type" => "Training Institution",
                "code" => "U049",
                "phone_no" => 756515211,
                "box_no" => 0
            ],
            [
                "id" => 3,
                "institution_name" => "ARUA SCHOOL OF COMPREHENSIVE NURSING",
                "short_name" => "ARUA",
                "institution_location" => "ARUA",
                "institution_type" => "Training Institution",
                "code" => "U001",
                "phone_no" => 782405989,
                "box_no" => 772515507
            ],
            [
                "id" => 4,
                "institution_name" => "BUSOGA UNIVERSITY SCHOOL OF NURSING",
                "short_name" => "BUSOGA",
                "institution_location" => "IGANGA",
                "institution_type" => "Training Institution",
                "code" => "U040",
                "phone_no" => 772393466,
                "box_no" => 775338964
            ],
            [
                "id" => 5,
                "institution_name" => "BUTABIKA SCHOOL OF PSYCHIATRIC NURSING",
                "short_name" => "BUTABIKA",
                "institution_location" => "KAMPALA",
                "institution_type" => "Training Institution",
                "code" => "U002",
                "phone_no" => 783692760,
                "box_no" => 751775407
            ],
            [
                "id" => 6,
                "institution_name" => "FLORENCE NIGHTINGALE SCHOOL OF NURSING AND MIDWIFE",
                "short_name" => "FLORENCE",
                "institution_location" => "APAC",
                "institution_type" => "Training Institution",
                "code" => "U037",
                "phone_no" => 772539049,
                "box_no" => 782060337
            ],
            [
                "id" => 7,
                "institution_name" => "FORTPORTAL INTERNATIONAL NURSING SCHOOL",
                "short_name" => "FORTPORTAL",
                "institution_location" => "KABAROLE",
                "institution_type" => "Training Institution",
                "code" => "U046",
                "phone_no" => 772933951,
                "box_no" => 782386662
            ],
            [
                "id" => 8,
                "institution_name" => "GOOD SAMARITAN INTERNATIONAL SCHOOL OF NURSING",
                "short_name" => "GOOD SAMARITAN",
                "institution_location" => "LIRA",
                "institution_type" => "Training Institution",
                "code" => "U038",
                "phone_no" => 782602722,
                "box_no" => 772825332
            ],
            [
                "id" => 9,
                "institution_name" => "GULU SCHOOL OF NURSING",
                "short_name" => "GULU",
                "institution_location" => "GULU",
                "institution_type" => "Training Institution",
                "code" => "U043",
                "phone_no" => 772587562,
                "box_no" => 757770162
            ],
            [
                "id" => 10,
                "institution_name" => "HOIMA SCHOOL OF NURSING AND MIDWIFERY",
                "short_name" => "HOIMA",
                "institution_location" => "HOIMA",
                "institution_type" => "Training Institution",
                "code" => "U044",
                "phone_no" => 772654080,
                "box_no" => 774113398
            ],
            [
                "id" => 11,
                "institution_name" => "IBANDA SCHOOL OF MIDWIFERY AND COMPREHENSIVE NURSI",
                "short_name" => "IBANDA",
                "institution_location" => "IBANDA",
                "institution_type" => "Training Institution",
                "code" => "U003",
                "phone_no" => 772885074,
                "box_no" => 772639040
            ],
            [
                "id" => 12,
                "institution_name" => "IGANGA SCHOOL OF NURSING AND MIDWIFERY",
                "short_name" => "IGANGA",
                "institution_location" => "IGANGA",
                "institution_type" => "Training Institution",
                "code" => "U041",
                "phone_no" => 772330494,
                "box_no" => 77898778
            ],
            [
                "id" => 13,
                "institution_name" => "INTERNATIONAL INSTITUTE OF HEALTH SCIENCES JINJA",
                "short_name" => "INT. INST. OF HLTH SCI.",
                "institution_location" => "JINJA",
                "institution_type" => "Training Institution",
                "code" => "U005",
                "phone_no" => 752954117,
                "box_no" => 775064606
            ],
            [
                "id" => 15,
                "institution_name" => "ISHAKA ADVENTIST SCHOOL OF NURSING",
                "short_name" => "ISHAKA",
                "institution_location" => "BUSHENYI",
                "institution_type" => "Training Institution",
                "code" => "U006",
                "phone_no" => 772529503,
                "box_no" => 772562207
            ],
            [
                "id" => 17,
                "institution_name" => "JINJA SCHOOL OF NURSING AND MIDWIFERY",
                "short_name" => "JINJA",
                "institution_location" => "JINJA",
                "institution_type" => "Training Institution",
                "code" => "U007",
                "phone_no" => 772558374,
                "box_no" => 782546701
            ],
            [
                "id" => 18,
                "institution_name" => "KABALE INSTITUITE OF HEALTH SCIENCES",
                "short_name" => "KABALE INS",
                "institution_location" => "KABALE",
                "institution_type" => "Training Institution",
                "code" => "U033",
                "phone_no" => 772345628,
                "box_no" => 0
            ],
            [
                "id" => 19,
                "institution_name" => "KABALE SCHOOL OF COMPREHENSIVE NURSING",
                "short_name" => "KABALE SCH",
                "institution_location" => "KABALE",
                "institution_type" => "Training Institution",
                "code" => "U008",
                "phone_no" => 774402429,
                "box_no" => 772665219
            ],
            [
                "id" => 20,
                "institution_name" => "KAGANDO SCHOOL OF NURSING AND MIDWIFERY",
                "short_name" => "KAGANDO",
                "institution_location" => "KASESE",
                "institution_type" => "Training Institution",
                "code" => "U009",
                "phone_no" => 772974587,
                "box_no" => 782540065
            ],
            [
                "id" => 21,
                "institution_name" => "KALUNGI SCHOOL OF NURSING",
                "short_name" => "KALUNGI",
                "institution_location" => "MASAKA",
                "institution_type" => "Training Institution",
                "code" => "U048",
                "phone_no" => 772440173,
                "box_no" => 772357863
            ],
            [
                "id" => 22,
                "institution_name" => "KAMPALA INTERNATIONAL UNIVERSITY",
                "short_name" => "KIU",
                "institution_location" => "MBARARA",
                "institution_type" => "Training Institution",
                "code" => "U011",
                "phone_no" => 0,
                "box_no" => 0
            ],
            [
                "id" => 23,
                "institution_name" => "KAMPALA INT.UNIVERSITY (KIU) WESTERN CAMPUS",
                "short_name" => "KAMPALA INT.UNIV. (KIU)",
                "institution_location" => "ISHAKA",
                "institution_type" => "Training Institution",
                "code" => "U011",
                "phone_no" => 702926554,
                "box_no" => 774185234
            ],
            [
                "id" => 24,
                "institution_name" => "KAMPALA UNIVERSITY SCHOOL OF NURSING",
                "short_name" => "KAMPALA UNIV. SCH.",
                "institution_location" => "KAMPALA",
                "institution_type" => "Training Institution",
                "code" => "U012",
                "phone_no" => 782697910,
                "box_no" => 77898778
            ],
            [
                "id" => 25,
                "institution_name" => "KAMULI SCHOOL OF NURSING AND MIDWIFERY",
                "short_name" => "KAMULI",
                "institution_location" => "KAMULI",
                "institution_type" => "Training Institution",
                "code" => "U013",
                "phone_no" => 782529642,
                "box_no" => 782119533
            ],
            [
                "id" => 26,
                "institution_name" => "KIBULI MOSLEM HOSPITAL HEALTH TRAINING SCHOOL",
                "short_name" => "KIBULI",
                "institution_location" => "KAMPALA",
                "institution_type" => "Training Institution",
                "code" => "U014",
                "phone_no" => 701198441,
                "box_no" => 782086391
            ],
            [
                "id" => 27,
                "institution_name" => "KISIIZI SCHOOL OF NURSING AND MIDWIFERY",
                "short_name" => "KISIIZI",
                "institution_location" => "RUKUNGIRI",
                "institution_type" => "Training Institution",
                "code" => "U015",
                "phone_no" => 772372739,
                "box_no" => 782594072
            ],
            [
                "id" => 28,
                "institution_name" => "KIWOKO SCHOOL OF NURSING AND MIDWIFERY",
                "short_name" => "KIWOKO",
                "institution_location" => "LUWEERO",
                "institution_type" => "Training Institution",
                "code" => "U016",
                "phone_no" => 772972577,
                "box_no" => 774228578
            ],
            [
                "id" => 29,
                "institution_name" => "KULUVA SCHOOL OF COMPREHENSIVE NURSING",
                "short_name" => "KULUVA",
                "institution_location" => "ARUA",
                "institution_type" => "Training Institution",
                "code" => "U017",
                "phone_no" => 787109483,
                "box_no" => 782359550
            ],
            [
                "id" => 30,
                "institution_name" => "LIRA SCHOOL OF COMPREHENSIVE NURSING",
                "short_name" => "LIRA",
                "institution_location" => "LIRA",
                "institution_type" => "Training Institution",
                "code" => "U019",
                "phone_no" => 782875581,
                "box_no" => 772605961
            ],
            [
                "id" => 31,
                "institution_name" => "MASAKA SCHOOL OF COMPREHENSIVE NURSING",
                "short_name" => "MASAKA",
                "institution_location" => "MASAKA",
                "institution_type" => "Training Institution",
                "code" => "U020",
                "phone_no" => 782313992,
                "box_no" => 0
            ],
            [
                "id" => 32,
                "institution_name" => "MAYANJA MEMORIAL MEDICAL TRAINING INSTITUTE",
                "short_name" => "MAYANJA",
                "institution_location" => "MBARARA",
                "institution_type" => "Training Institution",
                "code" => "U034",
                "phone_no" => 772368321,
                "box_no" => 0
            ],
            [
                "id" => 33,
                "institution_name" => "MBALE SCHOOL OF NURSING AND MIDWIFERY",
                "short_name" => "MBALE",
                "institution_location" => "MBALE",
                "institution_type" => "Training Institution",
                "code" => "U042",
                "phone_no" => 772485811,
                "box_no" => 712846978
            ],
            [
                "id" => 34,
                "institution_name" => "MENGO SCHOOL OF NURSING AND MIDWIFERY",
                "short_name" => "MENGO",
                "institution_location" => "KAMPALA",
                "institution_type" => "Training Institution",
                "code" => "U022",
                "phone_no" => 772587613,
                "box_no" => 718430939
            ],
            [
                "id" => 35,
                "institution_name" => "MULAGO SCHOOL OF NURSING AND MIDWIFERY",
                "short_name" => "MULAGO",
                "institution_location" => "KAMPALA",
                "institution_type" => "Training Institution",
                "code" => "U023",
                "phone_no" => 774463085,
                "box_no" => 784043590
            ],
            [
                "id" => 36,
                "institution_name" => "MUTOLERE SCHOOL OF NURSING AND MIDWIFERY",
                "short_name" => "MUTOLERE",
                "institution_location" => "KISOLO",
                "institution_type" => "Training Institution",
                "code" => "U024",
                "phone_no" => 772850544,
                "box_no" => 772336381
            ],
            [
                "id" => 37,
                "institution_name" => "NGORA HOSPITAL-SCHOOL OF NURSING AND MIDWIFERY",
                "short_name" => "NGORA",
                "institution_location" => "NGORA",
                "institution_type" => "Training Institution",
                "code" => "U025",
                "phone_no" => 772375361,
                "box_no" => 772959078
            ],
            [
                "id" => 38,
                "institution_name" => "ST. FRANCIS'S HOSPITAL - NSAMBYA SCHOOL OF NURSING AND MIDWIFERY",
                "short_name" => "ST. FRANCIS'S HOSPITAL",
                "institution_location" => "KAMPALA",
                "institution_type" => "Training Institution",
                "code" => "U026",
                "phone_no" => 9879,
                "box_no" => 77898778
            ],
            [
                "id" => 39,
                "institution_name" => "PUBLIC HEALTH NURSES SCHOOL - KYAMBOGO",
                "short_name" => "PUBLIC HEALTH",
                "institution_location" => "KAMPALA",
                "institution_type" => "Training Institution",
                "code" => "U050",
                "phone_no" => 772886833,
                "box_no" => 77898778
            ],
            [
                "id" => 40,
                "institution_name" => "RAKAI COMMUNITY SCHOOL OF NURSING",
                "short_name" => "RAKAI",
                "institution_location" => "RAKAI",
                "institution_type" => "Training Institution",
                "code" => "U028",
                "phone_no" => 9879,
                "box_no" => 77898778
            ],
            [
                "id" => 41,
                "institution_name" => "LUBAGA HOSPITALS TRAINING SCHOOLS",
                "short_name" => "LUBAGA",
                "institution_location" => "KAMPALA",
                "institution_type" => "Training Institution",
                "code" => "U029",
                "phone_no" => 9879,
                "box_no" => 77898778
            ],
            [
                "id" => 42,
                "institution_name" => "SALEM SCHOOL OF NURSING",
                "short_name" => "SALEM",
                "institution_location" => "KAMPALA",
                "institution_type" => "Training Institution",
                "code" => "U035",
                "phone_no" => 9879,
                "box_no" => 77898778
            ],
            [
                "id" => 43,
                "institution_name" => "SOROTI SCHOOL OF COMPREHENSIVE NURSING",
                "short_name" => "SOROTI",
                "institution_location" => "SOROTI",
                "institution_type" => "Training Institution",
                "code" => "U030",
                "phone_no" => 0,
                "box_no" => 0
            ],
            [
                "id" => 44,
                "institution_name" => "ST ELIZA SCHOOL OF NURSING AND MIDWIFERY",
                "short_name" => "ST ELIZA",
                "institution_location" => "KAMPALA",
                "institution_type" => "Training Institution",
                "code" => "U039",
                "phone_no" => 9879,
                "box_no" => 77898778
            ],
            [
                "id" => 45,
                "institution_name" => "KAROLI LWANGA  SCHOOL OF NURSING AND MIDWIFERY-NYAKIBALE",
                "short_name" => "KALORI",
                "institution_location" => "KAMPALA",
                "institution_type" => "Training Institution",
                "code" => "U027",
                "phone_no" => 9879,
                "box_no" => 77898778
            ],
            [
                "id" => 46,
                "institution_name" => "ST MARY'S MIDWIFERY SCHOOL - KALONGO",
                "short_name" => "ST MARY'S",
                "institution_location" => "AGAGO",
                "institution_type" => "Training Institution",
                "code" => "U010",
                "phone_no" => 9879,
                "box_no" => 77898778
            ],
            [
                "id" => 47,
                "institution_name" => "ST. FRANCIS NYENGA SCHOOL OF  NURSING",
                "short_name" => "NYENGA",
                "institution_location" => "KAMPALA",
                "institution_type" => "Training Institution",
                "code" => "U036",
                "phone_no" => 9879,
                "box_no" => 77898778
            ],
            [
                "id" => 48,
                "institution_name" => "ST. JOHN SCHOOL OF NURSING",
                "short_name" => "ST. JOHN",
                "institution_location" => "KAMPALA",
                "institution_type" => "Training Institution",
                "code" => "U045",
                "phone_no" => 772466931,
                "box_no" => 77898778
            ],
            [
                "id" => 49,
                "institution_name" => "ST. KIZITO HOSPITAL - MATANY SCHOOL OF NURSING",
                "short_name" => "MATANY",
                "institution_location" => "LIRA",
                "institution_type" => "Training Institution",
                "code" => "U021",
                "phone_no" => 0,
                "box_no" => 0
            ],
            [
                "id" => 50,
                "institution_name" => "ST. MARY'S HOSPITAL - LACOR SCHOOL OF NURSING",
                "short_name" => "ST. MARY'S HOSPITAL",
                "institution_location" => "GULU",
                "institution_type" => "Training Institution",
                "code" => "U018",
                "phone_no" => 9879,
                "box_no" => 77898778
            ],
            [
                "id" => 51,
                "institution_name" => "ST. LAWRENCE VILLA MARIA SCHOOL OF NURSING AND MIDWIFERY",
                "short_name" => "VILLA MARIA ",
                "institution_location" => "MASAKA",
                "institution_type" => "Training Institution",
                "code" => "U031",
                "phone_no" => 772906724,
                "box_no" => 77898778
            ],
            [
                "id" => 52,
                "institution_name" => "VIRIKA SCHOOL OF NURSING AND MIDWIFERY",
                "short_name" => "VIRIKA",
                "institution_location" => "KABAROLE",
                "institution_type" => "Training Institution",
                "code" => "U032",
                "phone_no" => 9879,
                "box_no" => 77898778
            ],
            [
                "id" => 53,
                "institution_name" => "NTUNGAMO HEALTH TRAINING INSTITUTE",
                "short_name" => "NTUNGAMO",
                "institution_location" => "NTUNGAMO",
                "institution_type" => "Training Institution",
                "code" => "U051",
                "phone_no" => 9879,
                "box_no" => 77898778
            ],
            [
                "id" => 54,
                "institution_name" => "DAF SCHOOL OF NURSING AND MIDWIFERY",
                "short_name" => "DAF",
                "institution_location" => "LIRA",
                "institution_type" => "Training Institution",
                "code" => "U057",
                "phone_no" => 9879,
                "box_no" => 77898778
            ],
            [
                "id" => 55,
                "institution_name" => "MOUNTAINS OF THE MOON UNIVERSITY",
                "short_name" => "MOUNTAINS",
                "institution_location" => "KABAROLE",
                "institution_type" => "Training Institution",
                "code" => "U053",
                "phone_no" => 9879,
                "box_no" => 77898778
            ],
            [
                "id" => 56,
                "institution_name" => "BISHOP STUART UNIVERSITY",
                "short_name" => "BISHOP STUART",
                "institution_location" => "MBARARA",
                "institution_type" => "Training Institution",
                "code" => "U056",
                "phone_no" => 9879,
                "box_no" => 77898778
            ],
            [
                "id" => 57,
                "institution_name" => "UGANDA CHRISTIAN INSTITUTE SCHOOL OF NURSING AND M",
                "short_name" => "UCI",
                "institution_location" => "MUKONO",
                "institution_type" => "Training Institution",
                "code" => "U055",
                "phone_no" => 9879,
                "box_no" => 77898778
            ],
            [
                "id" => 58,
                "institution_name" => "TUMU MEDICAL TRAINING INSTITUTE",
                "short_name" => "TUMU",
                "institution_location" => "KAMPALA",
                "institution_type" => "Training Institution",
                "code" => "U052",
                "phone_no" => 9879,
                "box_no" => 77898778
            ],
            [
                "id" => 59,
                "institution_name" => "JERUSALEM SCHOOL OF NURSING AND MIDWIFERY",
                "short_name" => "JERUSALEM",
                "institution_location" => "KAMPALA",
                "institution_type" => "Training Institution",
                "code" => "U062",
                "phone_no" => 9879,
                "box_no" => 77898778
            ],
            [
                "id" => 60,
                "institution_name" => "MUKONO DIOSECE SCHOOL OF NURSING",
                "short_name" => "MUKONO",
                "institution_location" => "MUKONO",
                "institution_type" => "Training Institution",
                "code" => "U054",
                "phone_no" => 9879,
                "box_no" => 77898778
            ],
            [
                "id" => 61,
                "institution_name" => "LUGAZI SCHOOL OF NURSING AND MIDWIFERY",
                "short_name" => "LUGAZI",
                "institution_location" => "JINJA",
                "institution_type" => "Training Institution",
                "code" => "U058",
                "phone_no" => 9879,
                "box_no" => 77898778
            ],
            [
                "id" => 63,
                "institution_name" => "RUGARAMA SCHOOL OF NURSING",
                "short_name" => "RUGARAMA",
                "institution_location" => "RUKUNGIRI",
                "institution_type" => "Training Institution",
                "code" => "U063",
                "phone_no" => 9879,
                "box_no" => 77898778
            ],
            [
                "id" => 64,
                "institution_name" => "ISLAMIC UNIVERSITY IN UGANDA SCHOOL OF NURSING-MBA",
                "short_name" => "IUIU",
                "institution_location" => "MBALE",
                "institution_type" => "Training Institution",
                "code" => "U047",
                "phone_no" => 701050845,
                "box_no" => 77898778
            ],
            [
                "id" => 65,
                "institution_name" => "UGANDA NURSING SCHOOL BWINDI ",
                "short_name" => "BWINDI",
                "institution_location" => "KANUNGU",
                "institution_type" => "Training Institution",
                "code" => "U059",
                "phone_no" => 9879,
                "box_no" => 77898778
            ],
            [
                "id" => 66,
                "institution_name" => "KYETUME SCHOOL OF NURSING AND MIDWIFERY",
                "short_name" => "KYETUME",
                "institution_location" => "MASAKA",
                "institution_type" => "Training Institution",
                "code" => "U061",
                "phone_no" => 9879,
                "box_no" => 77898778
            ],
            [
                "id" => 67,
                "institution_name" => "ST.GERTRUDE NURSING SCHOOL",
                "short_name" => "ST.GATRUDE",
                "institution_location" => "KAMPALA",
                "institution_type" => "Training Institution",
                "code" => "U060",
                "phone_no" => 9879,
                "box_no" => 77898778
            ],
            [
                "id" => 68,
                "institution_name" => "ANNE NEHMA MEMORIAL INTERNATIONAL HEALTH SCIENCE I",
                "short_name" => "ANNE NEHMA",
                "institution_location" => "MARAHCA",
                "institution_type" => "Training Institution",
                "code" => "U064",
                "phone_no" => 9879,
                "box_no" => 77898778
            ],
            [
                "id" => 71,
                "institution_name" => "JOHNASS INTERNATIONAL COLLEGE OF HEALTH SCIENCES",
                "short_name" => "JOHNASS",
                "institution_location" => "JINJA",
                "institution_type" => "PRIVATE",
                "code" => "U066",
                "phone_no" => 754664881,
                "box_no" => 675
            ],
            [
                "id" => 72,
                "institution_name" => "LIJIF",
                "short_name" => "LIJIF",
                "institution_location" => "Kampala",
                "institution_type" => "PRIVATE",
                "code" => "U068",
                "phone_no" => 9,
                "box_no" => 9
            ],
            [
                "id" => 73,
                "institution_name" => "ST. FRANCIS HOSPITAL NSAMBYA TRAINING SCHOOL",
                "short_name" => "Nsam",
                "institution_location" => "Kampala",
                "institution_type" => "PNFP",
                "code" => "U026",
                "phone_no" => 9,
                "box_no" => 9
            ],
            [
                "id" => 76,
                "institution_name" => "ST. MARY'S MIDWIFERY SCHOOL - KALONGO",
                "short_name" => "KALONG",
                "institution_location" => "Napak",
                "institution_type" => "PFNP",
                "code" => "U010",
                "phone_no" => 90,
                "box_no" => 78
            ],
            [
                "id" => 78,
                "institution_name" => "Rwenzori School of Nursing and Midwifery",
                "short_name" => "Rwenzori",
                "institution_location" => "Kasese",
                "institution_type" => "PFP",
                "code" => "U067",
                "phone_no" => 782540065,
                "box_no" => 98
            ],
            [
                "id" => 79,
                "institution_name" => "AGULE NURSING AND MIDWIFERY SCHOOL",
                "short_name" => "AGULE",
                "institution_location" => "PARISA",
                "institution_type" => "PFP",
                "code" => "U065",
                "phone_no" => 234,
                "box_no" => 657
            ],
            [
                "id" => 82,
                "institution_name" => "MITYANA INSTITUTE OF NURSING AND MIDWIFERY",
                "short_name" => "MITYANA",
                "institution_location" => "MITYANA TOWN",
                "institution_type" => "PRIVATE FOR PROFIT",
                "code" => "U070",
                "phone_no" => 782841821,
                "box_no" => 675
            ],
            [
                "id" => 83,
                "institution_name" => "BWERA SCHOOL OF NURSING AND MIDWIFERY",
                "short_name" => "BWERA",
                "institution_location" => "BWERA TOWN COUNCIL",
                "institution_type" => "PRIVATE FOR PROFIT",
                "code" => "U073",
                "phone_no" => 758712029,
                "box_no" => 678
            ],
            [
                "id" => 84,
                "institution_name" => "BWEYALE SCHOOL OF NURSING AND MIDWIFERY",
                "short_name" => "BWEYALE",
                "institution_location" => "KIRYANDONGO DISTRICT",
                "institution_type" => "PRIVATE FOR PROFIT",
                "code" => "U069",
                "phone_no" => 772946847,
                "box_no" => 8790
            ],
            [
                "id" => 85,
                "institution_name" => "KING JAMES SCHOOL OF NURSING AND MIDWIFERY",
                "short_name" => "KING JAMES",
                "institution_location" => "LIRA",
                "institution_type" => "PRIVATE FOR PROFIT",
                "code" => "U072",
                "phone_no" => 772431403,
                "box_no" => 890
            ],
            [
                "id" => 87,
                "institution_name" => "St. Josephs Hosptial-Kitgum Nursing and Midwifery School",
                "short_name" => "KITGUM",
                "institution_location" => "Kitgum",
                "institution_type" => "PFNPs",
                "code" => "U071",
                "phone_no" => 772545058,
                "box_no" => 31
            ],
            [
                "id" => 88,
                "institution_name" => "INTRAHEALTH SCHOOL OF NURSING AND MIDWIFERY",
                "short_name" => "INTRAHEALTH",
                "institution_location" => "KAMPALA",
                "institution_type" => "MIDWIFERY",
                "code" => "IH005",
                "phone_no" => 773003113,
                "box_no" => 112
            ],
            [
                "id" => 90,
                "institution_name" => "BUGEMA UNIVERSITY - SCH. OF HEALTH AND NATURAL SCI.",
                "short_name" => "BUGEMA",
                "institution_location" => "WAKISO",
                "institution_type" => "INSTITUTION",
                "code" => "U074",
                "phone_no" => 2147483647,
                "box_no" => 6529
            ],
            [
                "id" => 91,
                "institution_name" => "ACCESS HEALTH TRAINING INSTITUTE NAKASEKE",
                "short_name" => "ACCESS",
                "institution_location" => "NAKASEKE",
                "institution_type" => "PRIVATE FOR PROFIT",
                "code" => "U075",
                "phone_no" => 772472453,
                "box_no" => 28993
            ],
            [
                "id" => 92,
                "institution_name" => "UGANDA MARTYRS SCHOOL OF NURSING AND MIDWIFERY KALIRO",
                "short_name" => "KALIRO",
                "institution_location" => "KALIRO",
                "institution_type" => "Training Institution",
                "code" => "U076",
                "phone_no" => 0,
                "box_no" => 0
            ],
            [
                "id" => 94,
                "institution_name" => "MOYO SCHOOL OF NURSING AND MIDWIFERY",
                "short_name" => "MOYO",
                "institution_location" => "MOYO",
                "institution_type" => "PRIVATE FOR PROFIT",
                "code" => "U079",
                "phone_no" => 773027712,
                "box_no" => 0
            ],
            [
                "id" => 95,
                "institution_name" => "MARACHA SCHOOL OF NURSING AND MIDWIFERY",
                "short_name" => "MARACHA",
                "institution_location" => "MARACHA OVUJO",
                "institution_type" => "INSTITUTION",
                "code" => "U081",
                "phone_no" => 774113398,
                "box_no" => 0
            ],
            [
                "id" => 96,
                "institution_name" => "MILDMAY UGANDA SCHOOL OF NURSING",
                "short_name" => "MILDMAY",
                "institution_location" => "WAKISO-NAZIBA HILL LWEZA",
                "institution_type" => "INSTITUTION",
                "code" => "U080",
                "phone_no" => 392080568,
                "box_no" => 0
            ],
            [
                "id" => 97,
                "institution_name" => "LUBEGA INSTITUTE OF NURSING AND MEDICAL SCIENCES",
                "short_name" => "LUBEGA",
                "institution_location" => "IGANGA, NAKALAMA BUSEI",
                "institution_type" => "INSTITUTION",
                "code" => "U077",
                "phone_no" => 772460130,
                "box_no" => 0
            ],
            [
                "id" => 98,
                "institution_name" => "LYANTONDE SCHOOL OF NURSING AND MIDWIFERY",
                "short_name" => "LYANTONDE",
                "institution_location" => "LYANTONDE",
                "institution_type" => "INSTITUTION",
                "code" => "U078",
                "phone_no" => 0,
                "box_no" => 0
            ],
            [
                "id" => 99,
                "institution_name" => "LEURA SCHOOL OF NURSING AND MIDWIFERY",
                "short_name" => "LEURA SCH. N/M",
                "institution_location" => "IGANGA",
                "institution_type" => "PRIVATE",
                "code" => "U083",
                "phone_no" => 772869297,
                "box_no" => 103
            ],
            [
                "id" => 100,
                "institution_name" => "INDIAN INSTITUTE OF HEALTH & ALLIED SCIENCE",
                "short_name" => "INDIAN INSTITUTE",
                "institution_location" => "KAMPALA",
                "institution_type" => "PRIVATE FOR PROFIT",
                "code" => "U082",
                "phone_no" => 702782318,
                "box_no" => 21234
            ],
            [
                "id" => 101,
                "institution_name" => "MASAKA SCHOOL OF NURSING AND HEALTH SCIENCES",
                "short_name" => "MASAKA SHSCI",
                "institution_location" => "MASAKA",
                "institution_type" => "PRIVATE FOR PROFIT",
                "code" => "U084",
                "phone_no" => 782082923,
                "box_no" => 0
            ],
            [
                "id" => 102,
                "institution_name" => "AMG BUGONGI COLLEGE OF NURSING AND MIDWIFERY",
                "short_name" => "AMG BUGONGI",
                "institution_location" => "KABWOHE",
                "institution_type" => "PRIVATE",
                "code" => "U085",
                "phone_no" => 782597868,
                "box_no" => 72
            ],
            [
                "id" => 103,
                "institution_name" => "IGANGA SCHOOL OF NURSING NDEJJE CAMPUS",
                "short_name" => "IGANGA NDEJJE",
                "institution_location" => "NDEJJE",
                "institution_type" => "PRIVATE",
                "code" => "U087",
                "phone_no" => 782255890,
                "box_no" => 418
            ],
            [
                "id" => 104,
                "institution_name" => "KUMI SCHOOL OF NURSING AND MIDWIFERY",
                "short_name" => "KUMI",
                "institution_location" => "KUMI",
                "institution_type" => "PRIVATE",
                "code" => "U086",
                "phone_no" => 772451325,
                "box_no" => 89
            ],
            [
                "id" => 105,
                "institution_name" => "ST. PETER'S SCHOOL OF NURSING AND MIDWIFERY FOUNDATION",
                "short_name" => "SEBEI SNM",
                "institution_location" => "SEBEI",
                "institution_type" => "PFP",
                "code" => "U088",
                "phone_no" => 703532959,
                "box_no" => 0
            ],
            [
                "id" => 106,
                "institution_name" => "MT. ELGON SCHOOL OF NURSING AND MIDWIFERY",
                "short_name" => "ELGON SNM",
                "institution_location" => "MBALE",
                "institution_type" => "PFP",
                "code" => "U089",
                "phone_no" => 787963229,
                "box_no" => 0
            ],
            [
                "id" => 107,
                "institution_name" => "LIFESPRING INSTITUTE OF NURSING AND HEALTH SCIENCES",
                "short_name" => "LIFESPRING",
                "institution_location" => "PALLISA",
                "institution_type" => "PFP",
                "code" => "U090",
                "phone_no" => 772526882,
                "box_no" => 0
            ],
            [
                "id" => 108,
                "institution_name" => "MUBENDE ANSWERED PRAYERS SCHOOL OF NURSING AND MIDWIFERY",
                "short_name" => "ANSWERED PRAYERS",
                "institution_location" => "MUBENDE",
                "institution_type" => "Training Institution",
                "code" => "U091",
                "phone_no" => 703859384,
                "box_no" => 11
            ],
            [
                "id" => 109,
                "institution_name" => "NKUMBA UNIVERSITY INSTITUTE OF NURSING AND MIDWIFERY ",
                "short_name" => "NKUMBA",
                "institution_location" => "NKUMBA",
                "institution_type" => "PFP",
                "code" => "U092",
                "phone_no" => 782503981,
                "box_no" => 0
            ],
            [
                "id" => 110,
                "institution_name" => "WAKISO COMPREHENSIVE INSTITUTE OF HEALTH SCIENCES",
                "short_name" => "WAKISO COMP.",
                "institution_location" => "WAKISO",
                "institution_type" => "PFP",
                "code" => "U094",
                "phone_no" => 775338964,
                "box_no" => 33642
            ],
            [
                "id" => 111,
                "institution_name" => "EVELYN SCHOOL OF NURSING AND MIDWIFERY",
                "short_name" => "EVELYN SNM",
                "institution_location" => "MBARARA",
                "institution_type" => "PFP",
                "code" => "U093",
                "phone_no" => 1120,
                "box_no" => 782594072
            ],
            [
                "id" => 112,
                "institution_name" => "DOKOLO SCHOOL OF NURSING AND MIDWIFERY",
                "short_name" => "DOKOLO",
                "institution_location" => "DOKOLO",
                "institution_type" => "PFP",
                "code" => "U095",
                "phone_no" => 772150129,
                "box_no" => 567
            ],
            [
                "id" => 113,
                "institution_name" => "ST. FRANCIS SCHOOL OF HEALTH SCIENCES",
                "short_name" => "ST. FRANCIS SCHOOL",
                "institution_location" => "U",
                "institution_type" => "PFP",
                "code" => "U096",
                "phone_no" => 0,
                "box_no" => 0
            ],
            [
                "id" => 114,
                "institution_name" => "KIRYOKYA COLLEGE OF HEALTH SCIENCES",
                "short_name" => "KIRYOKYA",
                "institution_location" => "MITYANA",
                "institution_type" => "PFP",
                "code" => "U099",
                "phone_no" => 752533767,
                "box_no" => 35467
            ],
            [
                "id" => 115,
                "institution_name" => "LUWERO SCHOOL OF NURSING AND MIDWIFERY",
                "short_name" => "LUWERO SCH. N&M",
                "institution_location" => "LUWERO",
                "institution_type" => "PFP",
                "code" => "U100",
                "phone_no" => 782255890,
                "box_no" => 72
            ],
            [
                "id" => 116,
                "institution_name" => "THE HARVEY INSTITUTE OF HEALTH SCIENCES - NKOZI",
                "short_name" => "THE HARVEY INST.",
                "institution_location" => "MPIGI",
                "institution_type" => "PFP",
                "code" => "U097",
                "phone_no" => 789952283,
                "box_no" => 21239
            ],
            [
                "id" => 117,
                "institution_name" => "ISLAMIC UNIVERSITY IN UGANDA - KABOJJA SCHOOL OF NURSING AND MIDWIFERY",
                "short_name" => "IUIU - KABOJJA",
                "institution_location" => "WAKISO",
                "institution_type" => "PFP",
                "code" => "U098",
                "phone_no" => 757720500,
                "box_no" => 0
            ],
            [
                "id" => 118,
                "institution_name" => "ST. AMBROSE INSTITUTE OF HEALTH SCIENCES - KAGADI",
                "short_name" => "ST. AMBROSE",
                "institution_location" => "KAGADI",
                "institution_type" => "PFP",
                "code" => "U102",
                "phone_no" => 772973153,
                "box_no" => 0
            ],
            [
                "id" => 119,
                "institution_name" => "ST. MATHIAS SCHOOL OF NURSING AND MIDWIFERY - RUBIRIZI",
                "short_name" => "ST. MATHIAS",
                "institution_location" => "RUBIRIZI",
                "institution_type" => "PFP",
                "code" => "U103",
                "phone_no" => 782622172,
                "box_no" => 0
            ],
            [
                "id" => 120,
                "institution_name" => "PRIME SCHOOL OF NURSING AND MIDWIFERY - KYENJOJO",
                "short_name" => "PRIME SNM",
                "institution_location" => "KYENJOJO",
                "institution_type" => "PFP",
                "code" => "U104",
                "phone_no" => 702298884,
                "box_no" => 0
            ],
            [
                "id" => 121,
                "institution_name" => "INTERNATIONAL SCHOOL OF NURSING AND MIDWIFERY - MAYA",
                "short_name" => "INTERNATIONAL SCH. MAYA",
                "institution_location" => "MAYA",
                "institution_type" => "PFP",
                "code" => "U105",
                "phone_no" => 2147483647,
                "box_no" => 0
            ],
            [
                "id" => 122,
                "institution_name" => "ST. MARYS INSTITUTE OF HEALTH SCIENCES - KOLE",
                "short_name" => "KOLE",
                "institution_location" => "KAMPALA",
                "institution_type" => "PFP",
                "code" => "U101",
                "phone_no" => 772688401,
                "box_no" => 18
            ],
            [
                "id" => 123,
                "institution_name" => "BUNDIBUGYO SCHOOL OF NURSING AND MIDWIFERY",
                "short_name" => "BUNDIBUGYO SNM",
                "institution_location" => "BUNDIBUGYO",
                "institution_type" => "PFP",
                "code" => "U106",
                "phone_no" => 0,
                "box_no" => 0
            ],
            [
                "id" => 124,
                "institution_name" => "KAABONG COLLEGE OF NURSING AND MIDWIFERY",
                "short_name" => "KABONG",
                "institution_location" => "KABONG",
                "institution_type" => "GOVERNMENT",
                "code" => "U107",
                "phone_no" => 0,
                "box_no" => 0
            ],
            [
                "id" => 125,
                "institution_name" => "SEMBABULE SCHOOL OF NURSING AND MIDWIFERY",
                "short_name" => "SEMBABULE",
                "institution_location" => "SSEMBABULE",
                "institution_type" => "PRIVATE",
                "code" => "U111",
                "phone_no" => 772498320,
                "box_no" => 1388
            ],
            [
                "id" => 126,
                "institution_name" => "PAK MEMORIAL TRINITY INSTITUTE OF NURSING AND MIDWIFERY KYENJOJO",
                "short_name" => "PAK MEMORIAL",
                "institution_location" => "KYENJOJO",
                "institution_type" => "PRIVATE",
                "code" => "U110",
                "phone_no" => 772870283,
                "box_no" => 3515
            ],
            [
                "id" => 127,
                "institution_name" => "DEFENCE FORCES INSTITUTE OF HEALTH SCIENCES",
                "short_name" => "DEFENCE FORCES",
                "institution_location" => "JINJA",
                "institution_type" => "GOVERNMENT",
                "code" => "U108",
                "phone_no" => 711911280,
                "box_no" => 600
            ],
            [
                "id" => 128,
                "institution_name" => "MAGANJO SCHOOL OF NURSING AND MIDWIFERY",
                "short_name" => "MAGANJO",
                "institution_location" => "KAWEMPE WAKISO",
                "institution_type" => "PRIVATE",
                "code" => "U109",
                "phone_no" => 393217558,
                "box_no" => 35569
            ],
            [
                "id" => 129,
                "institution_name" => "MUBENDE INSTITUTE OF NURSING AND MIDWIFERY",
                "short_name" => "MUBENDE INSTITUTE",
                "institution_location" => "MUBENDE",
                "institution_type" => "PFP",
                "code" => "U112",
                "phone_no" => 751303212,
                "box_no" => 7063
            ],
            [
                "id" => 130,
                "institution_name" => "ST. ANDREA KAAHWA INSTITUTE OF HEALTH SCIENCES - KAKUMIRO",
                "short_name" => "ST. ANDREA",
                "institution_location" => "KAKUMIRO",
                "institution_type" => "Training Institution",
                "code" => "U113",
                "phone_no" => 0,
                "box_no" => 0
            ],
            [
                "id" => 131,
                "institution_name" => "KIBOGA SCHOOL OF NURSING AND MIDWIFERY",
                "short_name" => "KIBOGA HEALTH",
                "institution_location" => "KIBOGA",
                "institution_type" => "Training Institution",
                "code" => "U114",
                "phone_no" => 393228084,
                "box_no" => 193
            ],
            [
                "id" => 132,
                "institution_name" => "Xx",
                "short_name" => "Xx",
                "institution_location" => "Xz",
                "institution_type" => "TFP",
                "code" => "23",
                "phone_no" => 0,
                "box_no" => 0
            ],
            [
                "id" => 133,
                "institution_name" => "VICTORIA SCHOOL OF NURSING AND MIDWIFERY - LIRA",
                "short_name" => "VICTORIA",
                "institution_location" => "LIRA",
                "institution_type" => "PFP",
                "code" => "U115",
                "phone_no" => 0,
                "box_no" => 0
            ],
            [
                "id" => 134,
                "institution_name" => "KAMPALA SCHOOL OF HEALTH SCIENCES",
                "short_name" => "KAMPALA ",
                "institution_location" => "KAMPALA SHS",
                "institution_type" => "",
                "code" => "U116",
                "phone_no" => 0,
                "box_no" => 0
            ],
            [
                "id" => 135,
                "institution_name" => "JOHN FISHER INSTITUTE OF HEALTH SCIENCES",
                "short_name" => "JOHN FISHER HIS",
                "institution_location" => "LIRA",
                "institution_type" => "PFP",
                "code" => "U117",
                "phone_no" => 782556170,
                "box_no" => 498
            ],
            [
                "id" => 136,
                "institution_name" => "MASAKA CITY SCHOOL OF NURSING AND HEALTH SCIENCE",
                "short_name" => "MASAKA CITY SNHS",
                "institution_location" => "MASAKA",
                "institution_type" => "PFP",
                "code" => "U118",
                "phone_no" => 709480400,
                "box_no" => 1087
            ],
            [
                "id" => 137,
                "institution_name" => "KIREKA NURSING AND MIDWIFERY TRAINING SCHOOL",
                "short_name" => "KIREKA NMTS",
                "institution_location" => "KIREKA",
                "institution_type" => "PFP",
                "code" => "U119",
                "phone_no" => 0,
                "box_no" => 0
            ],
            [
                "id" => 138,
                "institution_name" => "ST. ELIZABETH INSTITUTE OF NURSING AND MIDWIFERY",
                "short_name" => "ST. ELIZABETH INM.",
                "institution_location" => "MUKONO",
                "institution_type" => "PFP",
                "code" => "U120",
                "phone_no" => 0,
                "box_no" => 0
            ],
            [
                "id" => 139,
                "institution_name" => "AKALO SCHOOL OF NURSING AND MIDWIFERY",
                "short_name" => "AKALO SNM",
                "institution_location" => "KOLE",
                "institution_type" => "PFP",
                "code" => "U121",
                "phone_no" => 0,
                "box_no" => 0
            ],
            [
                "id" => 140,
                "institution_name" => "AGA KHAN UNIVERSITY SCHOOL OF NURSING AND MIDWIFERY",
                "short_name" => "AGA KHAN UNIVERSITY",
                "institution_location" => "KAMPALA",
                "institution_type" => "PFP",
                "code" => "U122",
                "phone_no" => 0,
                "box_no" => 0
            ],
            [
                "id" => 141,
                "institution_name" => "SAMI HEALTH SCIENCE INSTITUTE - NKOZI",
                "short_name" => "SAMI HSI- NKOZI",
                "institution_location" => "NKOZI",
                "institution_type" => "PFP",
                "code" => "U123",
                "phone_no" => 0,
                "box_no" => 0
            ],
            [
                "id" => 142,
                "institution_name" => "ST. ALOSIOUS MUBENDE INSTITUTE OF NURSING AND MIDWIFERY",
                "short_name" => "ST. ALOSIOUS INM",
                "institution_location" => "MUBENDE",
                "institution_type" => "PFP",
                "code" => "U125",
                "phone_no" => 0,
                "box_no" => 0
            ],
            [
                "id" => 143,
                "institution_name" => "METROPOLITAN SCHOOL OF NURSING AND MIDWIFERY",
                "short_name" => "METROPOLITAN SNM",
                "institution_location" => "MBARARA",
                "institution_type" => "PFP",
                "code" => "U124",
                "phone_no" => 704622555,
                "box_no" => 344
            ],
            [
                "id" => 144,
                "institution_name" => "DOUBLE CURE SCHOOL OF NURSING AND MIDWIFERY",
                "short_name" => "DOUBLE CURE",
                "institution_location" => "MPIGI",
                "institution_type" => "PFP",
                "code" => "U126",
                "phone_no" => 788511773,
                "box_no" => 100
            ]
        ];

        DB::table('institutions')->insert($data);
    }
}
