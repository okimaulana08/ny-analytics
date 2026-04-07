<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use App\Models\NovelWritingGuideline;
use Illuminate\Database\Seeder;

class NovelWritingGuidelineSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = AdminUser::first()?->id ?? 1;

        NovelWritingGuideline::updateOrCreate(
            ['name' => 'Panduan Drama Rumah Tangga Indonesia v1'],
            [
                'genre' => 'drama_rumah_tangga',
                'is_active' => true,
                'narrative_pov' => 'first_person',
                'target_chapter_word_count' => 1500,
                'language_style' => 'Bahasa Indonesia sehari-hari, kalimat pendek maks 20 kata, dialog natural, campuran gaul ringan boleh. Hindari: bahasa terlalu formal, deskripsi fisik panjang di awal, flashback tidak relevan, dialog kaku, resolusi terlalu cepat.',
                'system_prompt_prefix' => 'Kamu adalah penulis novel Indonesia berpengalaman khusus genre Drama Rumah Tangga yang laris di platform KBM App dan Fizzo. Target pembacamu adalah wanita dan ibu rumah tangga usia 30-55 tahun. Tulisan kamu menggunakan sudut pandang orang pertama (AKU), Bahasa Indonesia sehari-hari yang mengalir natural, emosional namun tidak berlebihan, dan selalu menjaga konsistensi karakter. Setiap chapter wajib diakhiri dengan cliffhanger atau pertanyaan menggantung.',
                'plot_structure_notes' => "3 babak:\n- Babak 1 (Ch.1-20): Dunia runtuh — perkenalan, konflik meledak, titik terendah\n- Babak 2 (Ch.21-50): Perlawanan — bangkit, plot twist besar, konflik memuncak\n- Babak 3 (Ch.51-80): Pembalasan — glow up, karma antagonis, HEA wajib\n\nPlot twist wajib di: Ch.9-10, Ch.25-30, Ch.45-50\nSetiap chapter HARUS berakhir cliffhanger/pertanyaan menggantung\nHEA (Happily Ever After) adalah WAJIB",
                'character_archetypes' => [
                    [
                        'name' => 'Protagonis',
                        'description' => 'Wanita 28-38 thn, kuat tapi rapuh emosional, punya 1 kelemahan fatal. Arc: menderita → melawan → bangkit & bahagia',
                    ],
                    [
                        'name' => 'Antagonis Suami',
                        'description' => 'Tampak sempurna di luar, punya justifikasi internal. Jangan 100% jahat. Nasib: menyesal terlambat / karma',
                    ],
                    [
                        'name' => 'Pelakor',
                        'description' => '3 tipe: Premium (manipulatif+cerdas), Klasik (muda+naif), Mengejutkan (orang dekat=efek drama max)',
                    ],
                    [
                        'name' => 'Mertua Antagonis',
                        'description' => 'Punya power finansial/sosial, selalu bela anak kandung, nasib: akhirnya sadar',
                    ],
                    [
                        'name' => 'Love Interest Kedua',
                        'description' => 'Opsional, muncul Ch.25-40, tidak boleh sempurna — harus ada konflik kecil',
                    ],
                ],
                'forbidden_content' => 'Deskripsi fisik berlebihan di awal chapter; flashback panjang tanpa relevansi; dialog terlalu sopan/kaku; resolusi terlalu cepat; chapter tanpa ending menggantung; kalimat serapan Inggris tidak perlu; bahasa telenovela berlebihan.',
                'content_guidelines' => <<<'EOT'
## PANDUAN PENULISAN DRAMA RUMAH TANGGA INDONESIA

### Gaya Bahasa
- Sudut pandang orang pertama (AKU) — paling populer di KBM/Fizzo
- Kalimat pendek, maks 20 kata per kalimat
- Dialog natural, campuran bahasa gaul ringan boleh
- Emosional tapi tidak lebay
- Hindari narasi terlalu panjang tanpa dialog

### Formula Hook Pembuka (pilih sesuai situasi)
**Perselingkuhan:**
- "Aku tidak berencana pulang lebih awal hari itu. Tapi Tuhan rupanya punya rencana lain."
- "Pesan itu masuk saat aku sedang memasak makan malam. Dari nomor yang tidak kukenal, tapi isinya... menghancurkan segalanya."
- "Lima tahun pernikahan. Dan aku baru tahu hari ini bahwa tidak ada satu detik pun yang nyata."

**Poligami:**
- "Mas, aku perlu bicara sesuatu yang penting." Ia duduk di depanku dengan wajah yang sudah kusiapkan untuk mendengar kabar baik. Yang datang justru sebaliknya."
- "Undangan pernikahan itu tergeletak di atas meja dapur. Nama mempelai pria: suamiku."

**KDRT:**
- "Untuk keseratus kalinya, aku berjanji pada diriku sendiri: ini yang terakhir. Tapi janji itu sudah terlalu sering kulanggar."
- "Lebam di lenganku sudah memudar. Tapi yang di hati — entah kapan sembuhnya."

**Chapter tengah (setelah konflik puncak):**
- "Tiga bulan berlalu. Aku sudah tidak menangis lagi. Aku sudah lupa caranya."
- "Kata orang, waktu menyembuhkan segalanya. Bohong. Waktu hanya mengajarkan kita cara menyembunyikan luka."

**Hook cliffhanger (akhir chapter):**
- "Dan saat itulah aku melihatnya. Bersama seseorang yang tidak seharusnya ada di sana."
- "Telepon itu berdering lagi. Nomor yang sama. Kali ini aku mengangkatnya."
- "Ia tersenyum. Dan senyum itu... senyum yang sama dengan yang dulu membuatku jatuh cinta. Tapi sekarang hanya membuat perutku mual."

### Struktur Plot per Sub-genre

**Drama Perselingkuhan:**
- Ch.1-8: Kehidupan "sempurna" yang mulai retak — tanda-tanda kecil yang diabaikan
- Ch.9: TWIST — bukti perselingkuhan ditemukan secara tidak sengaja
- Ch.10-20: Konfrontasi, penyangkalan, keputusan
- Ch.21-25: Protagonis mulai bangkit/menemukan kekuatan
- Ch.26: TWIST BESAR — pelakor hamil / minta cerai resmi / ada pihak ketiga yang terlibat
- Ch.27-40: Perlawanan protagonis, dukungan dari unexpected ally
- Ch.41: PUNCAK — protagonis mengambil keputusan final
- Ch.42-50: Proses pemulihan, karma antagonis mulai berjalan
- Ch.51-60: Glow up protagonis, kemunculan love interest baru (opsional)
- Ch.61+: HEA — protagonis bahagia, antagonis mendapat karma

**Drama Poligami:**
- Ch.1-10: Kehidupan normal, lalu suami mulai berubah
- Ch.11: TWIST — protagonis menemukan suami sudah menikah diam-diam
- Ch.12-25: Tekanan dari mertua, suami bimbang
- Ch.26-30: TWIST — motif tersembunyi terungkap (uang? warisan? paksaan?)
- Ch.31-45: Dua istri bersatu melawan keluarga suami, atau jalan sendiri-sendiri
- Ch.46+: HEA untuk protagonis

**Drama KDRT:**
- Ch.1-8: Cinta yang perlahan menjadi penjara
- Ch.9-15: Eskalasi kekerasan, protagonis terisolasi
- Ch.16: TURNING POINT — ada kejadian yang memaksa protagonis bertindak
- Ch.17-30: Proses melarikan diri, bantuan dari pihak luar
- Ch.31-36: Suami mencari / mencoba kembali dengan wajah baru
- Ch.37+: Protagonis membangun kehidupan baru, HEA

**Drama Pernikahan Kontrak:**
- Ch.1-5: Pernikahan kontrak dengan syarat tertentu
- Ch.6-11: Mulai ada perasaan di salah satu pihak
- Ch.12: TWIST — salah satu pihak jatuh cinta
- Ch.13-25: Menyembunyikan perasaan, konflik karena kontrak
- Ch.26-35: Rahasia masa lalu salah satu pihak terungkap
- Ch.36-45: Ujian terbesar hubungan mereka
- Ch.46+: HEA — kontrak berakhir, cinta sesungguhnya dimulai

### Template Narasi Emosi

**Sedih/Hancur:**
"Aku tidak menangis. Air mata itu sudah habis sejak [waktu]. Yang ada sekarang hanya rasa kosong yang menganga di dada, seperti ada yang mencabut sesuatu dari dalam diriku dan tidak mengembalikannya."

**Marah:**
"Ada yang mendidih di dadaku. Bukan panas api, tapi panas es — dingin, tajam, dan jauh lebih berbahaya."

**Bangkit:**
"Cukup. Kata itu terdengar sangat kecil, tapi aku bisa merasakannya bergema di seluruh tubuhku. Cukup sudah aku membiarkan semua ini."

**Mencintai:**
"Aku tidak pernah meminta untuk jatuh cinta lagi. Tapi rupanya hati tidak pernah benar-benar mau mendengarkan kepalaku."

### Hook Library — 30+ Contoh Siap Pakai

1. "Pernikahan kami sempurna di mata orang lain. Sayangnya, aku bukan orang lain."
2. "Ia pulang pukul dua pagi untuk ketiga kalinya minggu ini. Dan aku sudah berhenti bertanya alasannya."
3. "Nama di layar ponselnya adalah nama yang selalu disebut dalam mimpiku sebagai ancaman."
4. "Anakku bertanya kenapa Ayah tidak pernah makan malam bersama kami lagi. Aku tidak punya jawaban yang tidak akan membuatnya menangis."
5. "Lima tahun lalu, ia berjanji akan selalu ada. Hari ini aku berdiri sendirian di rumah sakit, dan ia tidak mengangkat teleponku."
6. "Satu pesan. Tiga kata. Dan seluruh hidupku runtuh."
7. "Aku menemukan lipstik itu di saku kemejanya. Warna yang tidak pernah kupakai."
8. "Mereka bilang perempuan selalu tahu. Mereka benar. Aku tahu sejak lama. Aku hanya memilih untuk tidak percaya."
9. "Mertua memandangku seperti duri yang mengganggu. Suamiku memandangku seperti... tidak memandangku sama sekali."
10. "Hari ini ulang tahun pernikahan kami yang ke-7. Ia lupa. Untuk pertama kalinya, aku tidak mengingatkannya."
11. "Ada wanita lain. Aku tahu itu. Yang belum kutahu adalah — sudah berapa lama."
12. "Ia mencintaiku dengan cara yang menyakitkan. Dan aku cukup bodoh untuk tetap bertahan."
13. "Kata dokter, lebam ini dari jatuh. Kami sama-sama tahu itu bohong. Kami sama-sama berpura-pura percaya."
14. "Ini bukan pertama kalinya ia meminta maaf. Dan ini bukan terakhir kalinya aku memaafkan. Atau setidaknya, dulu begitu."
15. "Undangan itu datang dengan nama yang sudah kukenal. Nama suamiku, bersanding dengan nama yang bukan namaku."
EOT,
                'created_by' => $adminId,
            ]
        );

        // =====================================================================
        // HORROR
        // =====================================================================
        NovelWritingGuideline::updateOrCreate(
            ['name' => 'Panduan Horror Populer Indonesia v1'],
            [
                'genre' => 'horror',
                'is_active' => true,
                'narrative_pov' => 'first_person',
                'target_chapter_word_count' => 1800,
                'language_style' => 'Bahasa Indonesia sehari-hari bercampur atmosfer gelap. Kalimat bisa lebih panjang saat membangun suasana (maks 30 kata), tapi tetap mengalir natural. Dialog singkat dan penuh ketegangan. Hindari deskripsi gore berlebihan — ketakutan yang tidak terlihat jauh lebih menakutkan daripada yang terlihat.',
                'system_prompt_prefix' => 'Kamu adalah penulis horror Indonesia berpengalaman yang karya-karyanya viral di platform KBM App dan Fizzo. Inspirasimu adalah kisah KKN di Desa Penari, Pengabdi Setan, dan urban legend Nusantara. Tulisan kamu membangun rasa takut perlahan-lahan seperti air yang mendidih — pembaca tidak sadar sudah dalam bahaya sampai terlambat. Kamu ahli menggunakan folklore Jawa, Sunda, Bali, dan Melayu sebagai fondasi horor yang terasa nyata dan dekat. Sudut pandang orang pertama (AKU) membuat pembaca merasakan langsung setiap detik ketakutan sang tokoh.',
                'plot_structure_notes' => "3 babak horror:\n- Babak 1 (Ch.1-15): Kedatangan & keanehan — protagonis masuk ke lingkungan baru (desa, rumah tua, lokasi KKN, dll), tanda-tanda kecil yang diabaikan, atmosfer mencekam dibangun perlahan\n- Babak 2 (Ch.16-45): Eskalasi — kejadian supranatural makin intens, korban berjatuhan, protagonis mulai mencari tahu asal-muasal kutukan/entitas\n- Babak 3 (Ch.46+): Konfrontasi & resolusi — menghadapi entitas, ritual/solusi ditemukan, berhasil lolos atau tragis\n\nTwist wajib di: Ch.10-12 (keanehan pertama yang tidak bisa dijelaskan), Ch.25-30 (true nature entitas terungkap), Ch.40-45 (pengkhianatan/rahasia besar)\nEnding bisa: survived with trauma, pyrrhic victory, atau tragic — TIDAK HARUS happy ending\nSetiap chapter wajib diakhiri dengan cliffhanger atau sensasi creeping dread",
                'character_archetypes' => [
                    [
                        'name' => 'Protagonis Survivor',
                        'description' => 'Orang biasa (mahasiswa, pekerja muda, ibu rumah tangga) yang terjebak dalam situasi supranatural. Punya koneksi emosional dengan tempat/orang yang jadi kunci cerita. Arc: denial → trauma → bangkit melawan',
                    ],
                    [
                        'name' => 'Entitas/Antagonis Supranatural',
                        'description' => 'Berbasis folklore Indonesia: kuntilanak, pocong, genderuwo, wewe gombel, leak, tuyul. Punya ALASAN di balik kehadirannya — bukan sekadar jahat tapi punya backstory tragis. Semakin jelas terlihat, semakin tidak menakutkan.',
                    ],
                    [
                        'name' => 'Orang Dalam/Pengetahu',
                        'description' => 'Mbah/sesepuh desa, dukun, paranormal lokal. Tahu kebenaran tapi sering berbicara tidak langsung. Bisa jadi ally atau justru bagian dari masalah.',
                    ],
                    [
                        'name' => 'Teman/Rombongan',
                        'description' => 'Kalau protagonis tidak sendiri, teman-teman adalah variabel yang bisa jadi korban, penyelamat, atau pengkhianat. Satu teman yang skeptis wajib ada — untuk kontras dengan kejadian supranatural.',
                    ],
                ],
                'forbidden_content' => 'Gore/sadisme berlebihan tanpa tujuan naratif; setan yang langsung muncul tanpa build-up; dialog penjelasan panjang tentang lore di tengah ketegangan; protagonis yang bodoh tanpa alasan; ending yang terlalu mudah/deus ex machina.',
                'content_guidelines' => <<<'EOT'
## PANDUAN PENULISAN HORROR POPULER INDONESIA

### Prinsip Utama Horror yang Efektif
- **The Unknown is scarier than the Known** — deskripsikan bayangan, suara, sensasi. Jangan langsung tampilkan entitas secara penuh di awal.
- **Ground it in reality first** — mulai dengan hal-hal normal yang slowly menjadi off. Pembaca harus merasa "ini bisa terjadi padaku."
- **Use all senses** — bukan hanya visual. Bau tanah basah, suhu yang tiba-tiba turun, rasa logam di mulut.
- **Pacing matters** — chapter penuh ketegangan harus diselingi chapter yang lebih tenang (false security) untuk membuat pembaca lengah.

### Folklore Indonesia yang Populer
- **Kuntilanak**: Wanita berambut panjang, suara tawa menjauh = dekat, mendekat = jauh. Tertarik pada wanita hamil dan bayi.
- **Pocong**: Arwah yang belum dilepas tali kafannya. Melompat-lompat, muncul di tempat gelap.
- **Genderuwo**: Makhluk besar berbulu, mengganggu wanita, bisa menyerupai suami/pacar.
- **Wewe Gombel**: Menculik anak-anak yang ditelantarkan orang tuanya.
- **Leak (Bali)**: Penyihir yang bisa berubah wujud, kepala melayang dengan organ menggantung.
- **Tuyul**: Anak kecil gundul pencuri uang — lebih disturbing daripada menakutkan secara fisik.
- **Orang Bunian**: Makhluk halus yang mirip manusia, hidup di alam paralel.
- **Sundel Bolong**: Wanita hamil yang mati dibunuh, punggung berlubang, mencari bayi.

### Formula Hook Pembuka

**Kedatangan di tempat asing:**
- "Semua orang di desa itu tersenyum saat kami tiba. Tapi matanya — matanya tidak ikut tersenyum."
- "Rumah itu seharusnya kosong. Lampu di kamar paling atas itu seharusnya tidak menyala."
- "Mbah Sarni memperingatkan kami sebelum berangkat: jangan keluar setelah magrib. Kami tertawa. Kami harusnya tidak tertawa."

**Tanda pertama:**
- "Foto itu diambil kemarin malam. Kami berlima. Tapi di foto, ada enam sosok yang berdiri."
- "Suara itu bukan suara hewan. Aku tahu itu. Tapi otak manusia sangat pandai berbohong demi menjaga kewarasan."
- "Anak itu sudah berdiri di sudut kamarku selama sepuluh menit. Aku belum berani berbalik untuk memastikan apakah ia benar-benar ada."

**Escalation:**
- "Kemarin aku tidak percaya. Sekarang aku tidak punya pilihan lain."
- "Ada yang salah dengan [nama teman]. Sudah tiga hari. Tapi aku tidak bisa menjelaskan apa yang berubah — hanya perasaan bahwa yang berjalan di sampingku itu bukan ia lagi."

**Cliffhanger akhir chapter:**
- "Aku mematikan lampu. Dan dalam gelap itu, terdengar suara napas — padahal aku tahu aku sendirian di kamar ini."
- "Baru saja aku mau membalas chat-nya. Lalu sadar: ia meninggal tiga hari yang lalu."
- "Pintu itu terkunci dari dalam. Dari dalam yang tidak ada siapapun."

### Teknik Membangun Atmosfer

**Slow burn (bab awal):**
```
Sesuatu di desa ini terasa off. Bukan karena ada yang salah secara nyata — semuanya normal, bahkan terlalu normal. Jalanan bersih. Tidak ada anjing yang menggonggong. Anak-anak tidak bermain di luar. Hanya keheningan yang sangat rapi, seperti ada yang sengaja menyusunnya.
```

**Sensory horror:**
```
Bau itu yang pertama aku rasakan — manis yang membusuk, seperti bunga melati dicampur sesuatu yang tidak bisa kuidentifikasi. Lalu suhu turun tiba-tiba. Lalu bulu-bulu di lenganku berdiri. Dalam urutan itu, selalu dalam urutan itu.
```

**Psychological dread:**
```
Aku mulai mempertanyakan ingatanku sendiri. Apakah jendela itu memang selalu menghadap ke sana? Apakah pohon itu sudah ada sejak kemarin? Apakah aku yang berubah, atau memang dunia di sekitarku yang perlahan bergeser?
```

### Struktur Chapter Horror yang Efektif
1. **Opening**: False security atau langsung in medias res
2. **Build-up**: Detail kecil yang off, sensor pembaca mulai aktif
3. **Incident**: Kejadian supranatural — tapi jangan terlalu jelas
4. **Reaction**: Bagaimana protagonis merespons (denial/rasionalisasi/panik)
5. **Closing hook**: Revelasi kecil atau sensasi bahwa bahaya belum selesai
EOT,
                'created_by' => $adminId,
            ]
        );

        // =====================================================================
        // ACTION / ADVENTURE
        // =====================================================================
        NovelWritingGuideline::updateOrCreate(
            ['name' => 'Panduan Action/Adventure Populer Indonesia v1'],
            [
                'genre' => 'action_adventure',
                'is_active' => true,
                'narrative_pov' => 'third_person',
                'target_chapter_word_count' => 1600,
                'language_style' => 'Bahasa Indonesia dinamis dan bertenaga. Kalimat pendek saat adegan aksi (maks 10-12 kata), lebih panjang saat momen tenang. Dialog tajam, percaya diri, penuh subtext. Deskripsi aksi harus visual dan kronologis — pembaca harus bisa "melihat" setiap gerakan.',
                'system_prompt_prefix' => 'Kamu adalah penulis novel action/adventure Indonesia berpengalaman yang karyanya viral di platform digital. Inspirasimu adalah silat Melayu, operasi militer Indonesia, ekspedisi hutan Kalimantan, dan heist perkotaan Jakarta. Tulisan kamu membuat pembaca duduk di pinggir kursi — setiap chapter penuh energi, setiap adegan aksi terasa nyata dan sinematik. Kamu ahli memadukan aksi fisik yang menegangkan dengan karakter yang memiliki kedalaman emosional.',
                'plot_structure_notes' => "3 babak action/adventure:\n- Babak 1 (Ch.1-15): Panggilan petualangan — perkenalan protagonis di dunianya, insiden yang memaksa ia bertindak, pembentukan tim (jika ada), misi/quest dimulai\n- Babak 2 (Ch.16-45): Rintangan & eskalasi — serangkaian obstacles yang makin berat, satu anggota tim berkhianat atau gugur, sumber daya habis, protagonist di titik terlemah\n- Babak 3 (Ch.46+): Konfrontasi final — semua skill yang dipelajari digunakan, boss fight/final mission, resolusi\n\nBeat aksi wajib setiap 3-4 chapter\nTwist di: Ch.10-12 (motif sebenarnya terungkap), Ch.28-32 (pengkhianatan dari dalam), Ch.42-45 (kekalahan sebelum kemenangan)\nEnding: victory dengan harga yang harus dibayar — bukan kemenangan mudah",
                'character_archetypes' => [
                    [
                        'name' => 'Protagonis Pahlawan',
                        'description' => 'Mantan militer/intel/atlet silat/survivor yang punya skill spesifik. Bukan sempurna — punya cacat fisik atau trauma masa lalu. Arc: ragu → committed → mengorbankan sesuatu demi misi',
                    ],
                    [
                        'name' => 'Antagonis Sistemik',
                        'description' => 'Bukan sekadar penjahat — punya ideologi, sumber daya, dan justifikasi. Bisa: koruptor berkuasa, kartel narkoba, teroris dengan agenda, korporasi jahat. Harus cukup kuat sehingga kemenangan protagonis terasa layak.',
                    ],
                    [
                        'name' => 'Partner/Sidekick',
                        'description' => 'Melengkapi kemampuan protagonis — kalau protagonis brawn, partner adalah brain. Chemistry harus natural, bisa ada konflik internal tim. Satu partner yang awalnya tidak dipercaya lalu jadi tulang punggung tim.',
                    ],
                    [
                        'name' => 'Informan/Mentor',
                        'description' => 'Punya koneksi dan informasi yang dibutuhkan. Loyalitasnya ambiguous — apakah benar-benar membantu atau punya agenda sendiri? Bisa jadi korban atau justru villain tersembunyi.',
                    ],
                ],
                'forbidden_content' => 'Protagonis yang tidak pernah terluka/gagal; aksi tanpa konsekuensi fisik; villain yang bodoh tanpa alasan; plot armor yang terlalu tebal; dialog yang menjelaskan strategi secara detail di tengah aksi.',
                'content_guidelines' => <<<'EOT'
## PANDUAN PENULISAN ACTION/ADVENTURE POPULER INDONESIA

### Prinsip Aksi yang Efektif
- **Clarity over complexity** — pembaca harus selalu tahu siapa di mana melakukan apa. Adegan kacau boleh, tapi narasinya harus terkontrol.
- **Consequences matter** — setiap aksi punya harga. Luka, kelelahan, kehilangan amunisi, korban jiwa. Aksi tanpa konsekuensi terasa hampa.
- **Stakes escalation** — tiap konflik harus lebih besar dari sebelumnya. Dari perkelahian satu lawan satu ke menghadapi organisasi besar.
- **Character in action** — adegan aksi adalah cara terbaik menunjukkan karakter. Cara seseorang berkelahi, mengambil keputusan under pressure, adalah cermin kepribadiannya.

### Setting Populer Indonesia untuk Action
- **Jakarta Underground**: Jaringan bawah tanah kriminal, gedung-gedung pencakar langit yang menyimpan rahasia kotor, Tanah Abang sebagai hub aktivitas gelap
- **Hutan Kalimantan/Papua**: Ekspedisi survival, perburuan artefak, konflik dengan penambang ilegal atau kelompok bersenjata
- **Laut dan Kepulauan**: Pembajakan modern, penyelundupan, operasi di perairan terpencil
- **Daerah Konflik**: Perbatasan, daerah sengketa, operasi anti-teroris berbasis di Sulawesi/Maluku
- **Silat Underground**: Turnamen silat ilegal, dunia perguruan silat yang punya sisi gelap

### Formula Adegan Aksi

**Opening fight/action scene:**
```
Tidak ada peringatan. Tidak pernah ada.
Siku kanan datang dari sudut buta. Raka miringkan kepala setengah detik — cukup untuk membuatnya meleset. Tapi yang kedua, uppercut dari kiri, menghantam tulang rusuknya.
Ia jatuh satu langkah. Mengambil napas. Menilai situasi.
Tiga lawan. Dua dengan pisau. Satu di pintu.
Lima belas detik, kalkulasinya otomatis. Ia punya waktu lima belas detik sebelum yang di pintu itu bergerak.
```

**Infiltrasi/stealth:**
```
Dua penjaga. Pergantian setiap enam menit. Celah tiga puluh detik antara dua kamera sudut timur.
Cukup.
Dira menghitung mundur dalam kepala, mencocokkan ritme napasnya dengan jam di pergelangan tangan. Lima... empat... tiga...
```

**Chase scene:**
```
Ia berlari. Jalanan di Kemang ini dikenalnya seperti punggung tangannya sendiri — tapi malam membuat segalanya berbeda.
Suara sepatu di belakangnya makin dekat. Satu orang. Tapi yang lain pasti sudah mengepung dari arah lain.
Gang kiri. Warung tutup. Tembok dua meter.
Ia sudah membuat keputusan sebelum pikirannya selesai memproses.
```

**High-stakes dialogue (di tengah krisis):**
```
"Kita punya dua menit sebelum backup mereka tiba."
"Tidak cukup."
"Maka kita buat cukup."
```

### Hook Pembuka per Situasi

**Langsung in medias res:**
- "Pelukannya terasa seperti saudaraku sendiri. Sampai aku merasakan besi dingin di pinggangku — laras pistol yang diarahkan ke lambungku."
- "Misi ini seharusnya sederhana. Get in, get the package, get out. Tidak ada yang memberitahuku bahwa package itu bisa bicara."

**Establishing stakes:**
- "Dua puluh empat jam. Itu waktu yang diberikan mereka. Setelah itu, adikku tidak akan ada lagi."
- "Seluruh karir militerku mempersiapkanku untuk momen ini. Yang tidak dipersiapkannya: fakta bahwa targetku ternyata orang yang paling kukenal di dunia."

**Cliffhanger chapter:**
- "Baru saja aku mau menembak — ketika terdengar suara familiar itu. Suara yang tidak seharusnya ada di sini. Suara yang membekukan seluruh tubuhku."
- "Data itu ada di depanku. Dan nama yang tertulis di sana membuat pistolku terasa seperti beban sepuluh ton."
- "Ia menang. Kami kalah. Dan ia belum selesai — matanya mencari seseorang di antara kami."

### Teknik Membangun Ketegangan Non-Aksi
- **Countdown**: Waktu yang terus berkurang menciptakan tekanan tanpa perlu adegan fisik
- **Information asymmetry**: Pembaca tahu bahaya yang belum disadari protagonis
- **False safety**: Chapter tenang sebelum storm besar
- **Character doubt**: Momen protagonis mempertanyakan keputusannya sendiri sebelum bertindak
EOT,
                'created_by' => $adminId,
            ]
        );

        // =====================================================================
        // THRILLER
        // =====================================================================
        NovelWritingGuideline::updateOrCreate(
            ['name' => 'Panduan Thriller Populer Indonesia v1'],
            [
                'genre' => 'thriller',
                'is_active' => true,
                'narrative_pov' => 'first_person',
                'target_chapter_word_count' => 1600,
                'language_style' => 'Bahasa Indonesia tegang dan presisi. Kalimat pendek saat suspense memuncak, lebih panjang saat analisis/investigasi. Narasi internal protagonis sangat penting — pembaca harus mengikuti proses berpikirnya. Dialog mengandung subtext dan kebohongan yang terasa nyata.',
                'system_prompt_prefix' => 'Kamu adalah penulis thriller Indonesia berpengalaman yang karyanya menghipnotis pembaca dari halaman pertama. Inspirasimu adalah thriller psikologis Gillian Flynn, kejahatan korporat Indonesia, dan kasus kriminal yang menggemparkan publik. Kamu ahli membangun suspense melalui informasi yang ditahan, karakter yang tidak bisa dipercaya sepenuhnya, dan plot twist yang terasa logis setelah terungkap — bukan manipulatif. Sudut pandang orang pertama memungkinkan pembaca terjebak bersama protagonis dalam ketidakpastian.',
                'plot_structure_notes' => "3 babak thriller:\n- Babak 1 (Ch.1-15): Setup & inciting incident — perkenalan dunia normal protagonis, kejadian yang mengacaukan segalanya (pembunuhan, penghilangan, ancaman), protagonist terlibat secara personal\n- Babak 2 (Ch.16-45): Investigasi & rabbit hole — menggali kebenaran, setiap jawaban memunculkan pertanyaan baru, ancaman terhadap protagonis meningkat, orang-orang di sekitarnya tidak bisa dipercaya\n- Babak 3 (Ch.46+): Revelasi & konfrontasi — kebenaran sesungguhnya terungkap, konfrontasi dengan antagonis, resolusi yang meninggalkan kesan\n\nTwist WAJIB: Red herring di Ch.15-20 (tersangka palsu), Revelasi besar di Ch.35-40 (dunia protagonis ternyata tidak seperti yang dikira), True villain di Ch.45-50\nPacing: chapter pendek dan cepat saat suspense memuncak, lebih panjang saat penggalian clue",
                'character_archetypes' => [
                    [
                        'name' => 'Protagonis Investigator',
                        'description' => 'Bisa jurnalis, detektif amatir, korban, atau saksi mata yang tidak bisa diam. Punya kemampuan observasi tajam tapi juga blind spot personal yang membahayakannya. Unreliable narrator yang menarik: semakin yakin ia tentang kebenaran, pembaca semakin curiga.',
                    ],
                    [
                        'name' => 'Villain/Antagonis Tersembunyi',
                        'description' => 'Tidak boleh terasa seperti villain di awal — harus meyakinkan sebagai karakter simpatik atau netral. Punya motivasi yang logis (bukan sekadar "jahat"). Semakin lama tersembunyi, semakin besar impact revelasi.',
                    ],
                    [
                        'name' => 'Red Herring Character',
                        'description' => 'Karakter yang terasa mencurigakan dan mungkin adalah villain — tapi bukan. Harus punya alasan valid untuk terlihat mencurigakan. Pembaca dan protagonis harus sama-sama "tertipu".',
                    ],
                    [
                        'name' => 'Ally yang Ambiguous',
                        'description' => 'Membantu protagonis tapi selalu ada sesuatu yang tidak pas. Apakah benar-benar ally atau ada agenda tersembunyi? Loyalitasnya harus dipertanyakan sampai akhir.',
                    ],
                ],
                'forbidden_content' => 'Twist yang tidak ada clue-nya sama sekali (deus ex machina revelation); protagonis yang terlalu kompeten tanpa kelemahan; villain yang menjelaskan seluruh rencananya saat hampir menang; kebetulan yang terlalu banyak; plot hole yang tidak dijawab.',
                'content_guidelines' => <<<'EOT'
## PANDUAN PENULISAN THRILLER POPULER INDONESIA

### Prinsip Thriller yang Kuat
- **Information management** — kamu sebagai penulis tahu segalanya. Pembaca tidak. Dosis informasi dengan cermat: cukup untuk membuat mereka penasaran, tidak cukup untuk membuat mereka bisa menebak.
- **Every scene must do double duty** — setiap adegan harus sekaligus memajukan plot DAN membangun karakter atau dunia.
- **Trust is a weapon** — bangun kepercayaan pembaca terhadap karakter, lalu hancurkan. Berulang-ulang. Ini yang membuat thriller tidak bisa ditaruh sebelum selesai.
- **The personal stakes** — investigasi tentang kasus abstrak tidak menarik. Pembaca peduli ketika orang yang dicintai protagonis dalam bahaya, ketika reputasi/nyawa protagonis sendiri yang dipertaruhkan.

### Setting Populer Indonesia untuk Thriller
- **Korporasi Jakarta**: Manipulasi dalam perusahaan besar, pemalsuan laporan keuangan, pembunuhan yang ditutupi sebagai kecelakaan
- **Dunia Politik**: Skandal di balik figur publik, pemalsuan bukti, whistleblower yang diburu
- **Media & Influencer**: Kebenaran di balik persona online, stalker yang terorganisir, identitas yang dipalsukan
- **Lingkungan Elite**: Perumahan mewah dengan rahasia kelam, arisan yang menyimpan konspirasi, orang-orang dengan topeng sempurna
- **Akademia**: Plagiarisme yang berujung pembunuhan, eksperimen yang dirahasiakan, kartel mahasiswa

### Formula Hook Pembuka

**In medias res — sudah di tengah masalah:**
- "Aku tidak membunuhnya. Tapi aku tahu siapa yang melakukannya. Dan itu lebih berbahaya daripada jadi tersangkanya."
- "Pesan terakhir dari Nadia masuk pukul 02.47 pagi: 'Aku menemukan sesuatu. Jangan cerita ke siapapun.' Itu tiga hari yang lalu. Sejak itu, tidak ada kabar."

**Establishing the hook:**
- "Ada yang salah dengan kecelakaan itu. Polisi bilang murni kecelakaan. Tapi orang yang mati secara kecelakaan tidak meninggalkan pesan terenkripsi untuk saudaranya."
- "Aku wartawan. Aku terbiasa tidak percaya pada versi resmi cerita. Tapi bahkan aku tidak menyangka bahwa versi resmi kali ini adalah kebohongan berlapis-lapis."

**Paranoia building:**
- "Tiga orang yang tahu tentang dokumen itu. Satu sudah mati. Satu menghilang. Dan satu... adalah aku."
- "Seseorang masuk ke apartemenku tadi malam. Tidak ada yang diambil. Hanya ada satu hal yang berubah: foto di mejaku sudah dipindahkan, dibalik, dan diletakkan kembali."

**Cliffhanger:**
- "Rekaman CCTV itu jelas. Aku yang terlihat masuk ke ruangan itu pada waktu pembunuhan terjadi. Masalahnya: aku tidak ingat melakukannya."
- "Ia memberiku amplop sebelum pergi. Di dalamnya: foto diriku yang diambil tanpa aku tahu, dari jarak sangat dekat, dalam berbagai waktu berbeda. Dan tulisan tangan di belakang foto terakhir: 'Aku sudah selalu mengawasimu.'"
- "Nama di daftar itu adalah nama yang tidak seharusnya di sana. Nama yang akan mengubah segalanya jika aku benar dalam dugaanku. Nama yang membuat aku mempertanyakan tiga tahun terakhir hidupku."

### Teknik Membangun Suspense Tanpa Aksi
- **The withheld information**: Tokoh tahu sesuatu yang belum dibagi ke pembaca — atau pembaca tahu sesuatu yang tokoh tidak tahu
- **False resolution**: Tampak seolah misteri sudah terpecahkan, lalu satu detail kecil membongkar semuanya
- **Time pressure**: Deadline nyata — ancaman yang akan terjadi dalam hitungan jam/hari
- **Isolation**: Protagonis tidak bisa mempercayai siapapun termasuk yang paling dekat
- **The mundane detail**: Satu detail kecil yang tampak tidak penting di awal, jadi kunci di akhir — tanamkan sejak chapter 1-5
EOT,
                'created_by' => $adminId,
            ]
        );

        // =====================================================================
        // FANTASY
        // =====================================================================
        NovelWritingGuideline::updateOrCreate(
            ['name' => 'Panduan Fantasy Populer Indonesia v1'],
            [
                'genre' => 'fantasy',
                'is_active' => true,
                'narrative_pov' => 'third_person',
                'target_chapter_word_count' => 2000,
                'language_style' => 'Bahasa Indonesia kaya dan puitis untuk narasi dunia, tapi tetap mengalir saat dialog dan aksi. Nama karakter dan tempat menggunakan nuansa Nusantara (Jawa, Melayu, Sansekerta, atau kreasi orisinal yang terasa Indonesiawi). Kalimat naratif bisa lebih panjang untuk world-building, dialog tetap natural.',
                'system_prompt_prefix' => 'Kamu adalah penulis fantasy Indonesia berpengalaman yang membangun dunia-dunia luar biasa berbasis mitologi dan kearifan Nusantara. Inspirasi utamamu adalah wayang Jawa, kisah Majapahit dan Sriwijaya, makhluk mitologi Indonesia (naga, garuda, bidadari, siluman), serta sistem kepercayaan lokal yang kaya. Kamu ahli membangun magic system yang terasa konsisten dan organik, konflik epik yang punya akar personal, dan karakter yang berkembang melalui cobaan yang melampaui kemampuan manusia biasa.',
                'plot_structure_notes' => "Hero's Journey versi Indonesia:\n- Babak 1 (Ch.1-20): Dunia biasa & panggilan — protagonis di dunianya (desa, istana, pesantren desa tersembunyi), kejadian yang memaksa pergi, menemukan kekuatan/warisan, mentor muncul\n- Babak 2 (Ch.21-55): Perjalanan & ujian — melewati wilayah berbahaya, bertemu sekutu dan musuh, kekuatan makin besar tapi harga yang dibayar makin berat, mentor gugur atau berkhianat, kekalahan besar di tengah\n- Babak 3 (Ch.56+): Konfrontasi final — menghadapi dark lord/antagonis utama dengan semua yang telah dipelajari, pengorbanan besar, kemenangan yang mengubah dunia\n\nTwist wajib: Ch.15-18 (kebenaran tentang asal-usul protagonis), Ch.35-40 (ally utama berkhianat atau terungkap punya agenda sendiri), Ch.50-55 (protagonist harus memilih antara dua hal yang sama pentingnya)\nMagic system harus konsisten — tentukan aturannya di awal dan patuhi sepanjang cerita",
                'character_archetypes' => [
                    [
                        'name' => 'Protagonis Terpilih/Keturunan',
                        'description' => 'Anak desa/rakyat biasa yang punya warisan tersembunyi (keturunan raja, pemilik kekuatan langka, atau yang ditakdirkan). Harus punya kelemahan nyata agar tidak Mary Sue. Arc: ketidaktahuan → penemuan diri → pengorbanan → transcendence.',
                    ],
                    [
                        'name' => 'Dark Lord/Antagonis Epik',
                        'description' => 'Pernah menjadi hero atau memiliki tujuan mulia yang berubah ekstrem. Punya filosofi yang terasa masuk akal — bukan sekadar ingin berkuasa. Harus cukup kuat sehingga kemenangan protagonis terasa monumental.',
                    ],
                    [
                        'name' => 'Mentor/Guru',
                        'description' => 'Orang tua / bijak / pahlawan masa lalu yang melatih protagonis. Wajib punya rahasia yang berhubungan dengan backstory protagonis. Nasib: gugur secara heroik, atau terungkap punya agenda sendiri yang memaksa protagonis mandiri.',
                    ],
                    [
                        'name' => 'Kompanion Beragam',
                        'description' => 'Tim quest dengan kemampuan berbeda: pejuang, penyihir, pencuri/penjelajah, penyembuh. Masing-masing punya backstory dan arc sendiri. Chemistry dan konflik dalam tim harus organik.',
                    ],
                    [
                        'name' => 'Love Interest dengan Kompleksitas',
                        'description' => 'Bukan sekadar objek romansa — punya tujuan sendiri yang kadang bertentangan dengan protagonis. Hubungan romantis dibangun lambat melalui shared struggle, bukan insta-love.',
                    ],
                ],
                'forbidden_content' => 'Magic system tanpa aturan/batasan; protagonis yang menang terlalu mudah; dunia yang hanya jiplak Eropa medieval tanpa sentuhan Nusantara; karakter perempuan yang hanya jadi prize untuk protagonis laki-laki; info-dump panjang di chapter awal.',
                'content_guidelines' => <<<'EOT'
## PANDUAN PENULISAN FANTASY POPULER INDONESIA

### Membangun Dunia Nusantara Fantasy
- **Gunakan kearifan lokal sebagai fondasi** — sistem kasta Jawa, konsep karma dan dharma Hindu-Buddha, pengaruh Islam pesisir, adat istiadat Minang/Batak/Bali sebagai bahan baku world-building yang orisinal.
- **Nama dan bahasa** — gunakan Sansekerta, Jawa Kuno, Melayu Klasik untuk nama bangsawan/tempat sakral. Bahasa sehari-hari tetap natural.
- **Magic system berbasis budaya**: Tenaga dalam (silat), ilmu gaib kejawen, mantra Sanskrit, kekuatan elemen alam (api gunung berapi, laut selatan, hutan tropis).
- **Show the world through action** — hindari info-dump. Perkenalkan elemen dunia melalui interaksi karakter, bukan penjelasan langsung.

### Makhluk Mitologi Indonesia untuk Fantasy
- **Garuda**: Burung raksasa pembawa cahaya, simbol kekuatan dan kebenaran
- **Naga Nusantara**: Beda dari naga Eropa — lebih bijak, penjaga sungai/laut, bisa berbentuk manusia
- **Bidadari**: Makhluk kayangan yang bisa turun ke bumi, sering terlibat dengan manusia
- **Siluman Harimau**: Penjaga hutan, bisa manusia-harimau, punya kode moral sendiri
- **Gajah Mada sebagai archetype**: Mahapatih yang loyal, ambisius, punya sumpah yang menentukan nasibnya
- **Dewi Sri**: Dewi padi/kemakmuran, simbol kehidupan dan kesuburan tanah
- **Batara Kala**: Dewa waktu dan kematian, pemakan anak-anak yang lahir di hari tertentu

### Formula Hook Pembuka

**Establishing the world:**
- "Di Nusantara Tujuh, setiap anak lahir dengan satu kata tertulis di telapak tangannya — kata yang menentukan takdirnya. Aku lahir tanpa kata apapun. Dan itu, kata para tetua, adalah tanda yang paling berbahaya."
- "Istana Majawangi berdiri selama tiga ratus tahun tanpa pernah jatuh ke tangan musuh. Hari ini adalah hari ketiga ratusnya. Dan hari ini, aku yang membukakannya pintunya."

**Protagonist's ordinary world:**
- "Aku hanya seorang pembuat keris. Ayahku pembuat keris. Kakekku pembuat keris. Tidak ada yang istimewa dari tangan-tangan ini — sampai hari itu, ketika keris yang kubuat menerima takdirnya sendiri dan menolak takdir yang kusiapkan untuknya."

**Call to adventure:**
- "Utusan kerajaan itu membawa satu pesan: raja memanggilku. Bukan karena prestasinya — raja tidak tahu aku ada. Ia memanggilku karena sesuatu yang tersegel dalam diriku selama dua puluh tahun akhirnya mulai bocor."

**Cliffhanger:**
- "Api keris itu tidak padam saat dicelupkan ke air. Tidak padam saat ditanam dalam tanah. Tidak padam saat dibungkus dalam lapisan baja. Ia hanya padam ketika menyentuh dadaku — dan saat itu, aku merasakan sesuatu yang sudah lama tidur dalam diriku mulai terbangun."
- "Peta itu berakhir di sini. Di sinilah seharusnya Kota yang Hilang berada. Tapi yang ada di hadapanku bukan reruntuhan — melainkan istana yang hidup, bercahaya, dan penuh dengan orang-orang yang sudah kami anggap mati."

### Teknik World-building yang Tidak Membosankan
- **Iceberg principle**: Kamu tahu 90% duniamu, tapi hanya tampilkan 10% yang relevan dengan cerita
- **Conflict reveals world**: Tunjukkan sistem sosial melalui konflik, bukan penjelasan
- **Character as guide**: Karakter yang baru mengenal suatu tempat/konsep adalah cara alami memperkenalkan world-building
- **Magic dengan cost**: Setiap penggunaan kekuatan harus ada harganya — kelelahan, pengorbanan, risiko. Magic gratis membunuh tension.
EOT,
                'created_by' => $adminId,
            ]
        );

        // =====================================================================
        // COMEDY
        // =====================================================================
        NovelWritingGuideline::updateOrCreate(
            ['name' => 'Panduan Comedy Populer Indonesia v1'],
            [
                'genre' => 'comedy',
                'is_active' => true,
                'narrative_pov' => 'first_person',
                'target_chapter_word_count' => 1500,
                'language_style' => 'Bahasa Indonesia santai, relatable, banyak bahasa gaul kekinian yang natural. Kalimat pendek dan punchy untuk punchline. Narasi internal protagonis yang lucu dan self-deprecating. Dialog harus terdengar seperti orang betulan bicara. Timing comedy dalam teks: setup → konteks → punchline — jangan dijelaskan kenapa lucunya.',
                'system_prompt_prefix' => 'Kamu adalah penulis comedy Indonesia berpengalaman yang karyanya selalu bikin pembaca tertawa terbahak sambil tetap merasa terhubung secara emosional. Inspirasimu adalah slice-of-life Indonesia yang penuh keanehan, romcom yang awkward tapi manis, dan situasi kerja/kuliah yang universally relatable. Humor kamu tidak mengandalkan lelucon kotor atau menyinggung — tapi situasional, karakter-driven, dan sering kali poignant di balik kelucuannya. Setiap chapter punya minimal satu momen yang membuat pembaca tertawa keras dan satu momen yang membuat mereka merasakan sesuatu.',
                'plot_structure_notes' => "Struktur Comedy/Romcom Indonesia:\n- Babak 1 (Ch.1-15): Setup dunia & karakter — perkenalan protagonis yang relatable (dengan semua keanehan dan kegagalannya), inciting incident yang awkward, pertemuan pertama yang disaster\n- Babak 2 (Ch.16-45): Eskalasi situasi komedi — satu kebohongan/misunderstanding yang makin rumit, momen-momen cringe yang memuncak, perasaan yang mulai tumbuh di tengah kekacauan\n- Babak 3 (Ch.46+): Grand gesture & resolusi — protagonis menunjukkan sisi terbaiknya, grand misunderstanding diselesaikan, happy ending yang earned\n\nBeat komedi wajib setiap 2-3 chapter\nEmotional beat di: Ch.20-25 (momen serius yang genuine di tengah komedi), Ch.38-42 (darkest moment — bukan dark, tapi protagonist di titik paling hopeless secara komedi)\nHappy ending WAJIB — comedy tanpa happy ending adalah tragedi dengan jokes",
                'character_archetypes' => [
                    [
                        'name' => 'Protagonis yang Relatable (Disaster)',
                        'description' => 'Orang biasa dengan kemalangan luar biasa — bukan bodoh, tapi selalu berada di situasi paling awkward yang memungkinkan. Punya obsesi/hobi yang aneh, cara berpikir yang over-analitis, dan respons fight-or-flight yang selalu memilih flight (sampai akhirnya tidak).',
                    ],
                    [
                        'name' => 'Love Interest yang Tidak Terduga',
                        'description' => 'Bukan prince charming klasik. Punya quirk sendiri yang awalnya menyebalkan lalu jadi charming. Chemistry dibangun dari banter dan shared chaos, bukan first sight attraction.',
                    ],
                    [
                        'name' => 'Sahabat Bestie yang Chaos',
                        'description' => 'Pemberi saran yang tidak pernah benar tapi penuh keyakinan. Selalu ada untuk protagonist tapi sering memperburuk situasi dengan niat terbaik. Karakter yang paling sering mencuri scene.',
                    ],
                    [
                        'name' => 'Antagonis Komedi',
                        'description' => 'Bukan villain jahat — lebih ke rival yang pretentious, atasan yang tidak kompeten, atau mantan yang terlalu desperate untuk kelihatan baik. Harus cukup annoying untuk jadi hambatan tapi tidak sampai mengganggu tone komedi.',
                    ],
                ],
                'forbidden_content' => 'Humor yang merendahkan fisik seseorang; joke rasis/sexist; karakter perempuan yang hanya jadi bulan-bulanan tanpa agency; situasi dark tanpa resolution; ending sedih atau open-ended yang menggantung — comedy HARUS happy ending.',
                'content_guidelines' => <<<'EOT'
## PANDUAN PENULISAN COMEDY POPULER INDONESIA

### Prinsip Comedy yang Efektif dalam Novel
- **Comedy = Surprise + Truth** — hal yang lucu adalah hal yang tidak terduga TAPI terasa sangat benar. "Itu persis yang aku rasakan/lakukan!"
- **Timing lewat paragraf** — dalam teks, baris kosong sebelum punchline = jeda komedi. Gunakan baris pendek sendiri untuk punchline.
- **Self-deprecating narrator** — protagonist yang bisa menertawakan dirinya sendiri adalah yang paling loveable dan lucu.
- **The rule of three** — dua hal normal, ketiga yang tidak terduga. "Aku siapkan CV, portfolio, dan doa darurat."
- **Comedy and heart** — joke yang paling memorable adalah yang membuat pembaca tertawa DAN merasakan sesuatu. Pure slapstick tanpa emotional core cepat membosankan.

### Situasi Komedi yang Relatable Indonesia
- **Situasi kerja**: Rapat yang tidak perlu jadi email, atasan yang tidak ngerti teknologi, deadline chaos, WFH dengan background yang seharusnya profesional tapi tidak
- **Kehidupan kos/apartemen**: Tetangga yang tidak sopan, masalah dengan pemilik kos, memasak dengan kompor satu tungku untuk makan berminggu-minggu
- **Keluarga Indonesia**: Pertanyaan "kapan nikah" di setiap acara keluarga, ortu yang tiba-tiba tech-savvy tapi salah kaprah, mertua dengan standar tidak masuk akal
- **Dating modern**: Baca gesture salah, ghosting, date yang berjalan sangat berbeda dari ekspektasi, "kita ketemuan ya" yang tidak pernah terjadi
- **Transportasi & macet Jakarta**: Ojol yang tidak bisa menemukan lokasi, MRT di jam sibuk, macet yang membuat orang filsuf

### Formula Humor dalam Teks

**Setup + Konteks + Punchline:**
```
Aku datang lima menit lebih awal untuk menunjukkan profesionalisme.

Meeting-nya mundur tiga puluh menit.

Aku duduk di lobby sambil pura-pura scroll HP dengan ekspresi orang sibuk, padahal yang kubuka adalah thread Twitter tentang kucing.
```

**Self-deprecating narasi:**
```
Dalam skenario idealku, aku akan berjalan menuju mejanya dengan percaya diri, meletakkan kopi pesanannya, dan bilang sesuatu yang witty tapi tidak terlalu desperate.

Yang terjadi: aku tersandung karpet, kopi tumpah sebagian ke mejanya, dan hal witty yang keluar dari mulutku adalah "ups."

Ia menatapku.

"Kamu baik-baik saja?"

Tidak. Tapi aku mengangguk.
```

**Rule of three:**
```
Ada tiga hal yang aku persiapkan untuk hari ini: presentasi yang sudah direvisi dua belas kali, outfit yang sudah dipilih sejak kemarin, dan mental yang sudah diberi motivasi sejak subuh.

Yang aku lupa: file presentasinya masih di laptop rumah.
```

**Dialogue comedy:**
```
"Gimana kencannya?"
"Ia bertanya apakah aku percaya alien."
"...Dan?"
"Aku bilang tergantung definisi alien. Lalu kita debat empat puluh menit."
"Romantis sekali."
"Ia traktir es krim setelah itu. Sebagai tanda damai."
"Ini berhasil."
```

### Hook Pembuka

**Langsung chaos:**
- "Hari ini aku akan mengesankan bos baru dengan kedatangan tepat waktu. Sayangnya, alarm di HPku rupanya punya sense of humor yang lebih baik dari rencanaku."
- "Ada banyak cara bertemu cinta sejati. Di antrelan complain customer service, dengan rambut tidak sisir dan baju tidur karena work-from-home, bukan salah satunya. Atau seharusnya bukan."

**Stakes komedi:**
- "Satu kebohongan kecil. Itu yang kubutuhkan. Katakan saja kamu punya pacar, kata Rini, dan ibu tidak akan tanya lagi sampai lebaran depan. Simpel. Yang Rini tidak perhitungkan: aku tidak punya pacar, lebaran depan masih tiga bulan, dan ibu rupanya sudah memesan katering."
- "Nama di grup chat keluarga itu: 'Anak Mantu Cucu'. Anggotanya: seluruh keluarga besarku. Kecuali mantu dan cucu yang dimaksud, karena keduanya belum ada. Misi hidupku sekarang: keluar dari grup ini dengan cara yang terhormat."

**Cliffhanger komedi:**
- "Aku bisa jelaskan. Tapi itu butuh waktu, dan bos baru itu sudah berdiri di pintu dengan ekspresi yang belum kuidentifikasi apakah itu marah atau bingung. Semoga bingung. Aku lebih bisa berurusan dengan bingung."
- "Ia memandangku dengan ekspresi yang tidak bisa kubaca. Lalu bibirnya bergerak. Dan kata pertama yang keluar adalah: 'Kamu itu—' Pause panjang yang membuat jantungku hampir berhenti. '—lucu banget, sih.'"

### Emotional Beats di Tengah Komedi
Comedy terbaik punya momen yang genuine dan menyentuh — bukan melankoli, tapi warmth:
```
Di balik semua kekacauan hari ini, satu hal yang pasti: Rini mau nemenin aku tiga jam nungguin pengumuman itu, sambil main tebak-tebakan random supaya aku nggak keburu anxiety. Dia nggak tanya gimana rasanya. Dia tau.

Kadang persahabatan itu sesederhana itu.
```
EOT,
                'created_by' => $adminId,
            ]
        );
    }
}
