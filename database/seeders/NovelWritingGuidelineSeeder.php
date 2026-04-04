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
    }
}
