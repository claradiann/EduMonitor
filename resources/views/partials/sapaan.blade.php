{{-- Sapaan beranda dengan ornamen kawung dan kata motivasi harian.
     Variabel: $nama. Informasi penilaian tidak ditaruh di sini, tetapi dikirim lewat notifikasi. --}}
@php
    $jam = now()->hour;
    $salam = $jam < 11 ? 'Selamat pagi' : ($jam < 15 ? 'Selamat siang' : ($jam < 18 ? 'Selamat sore' : 'Selamat malam'));

    $motivasi = [
        'siswa' => [
            'Belajar sedikit setiap hari lebih kuat daripada belajar banyak semalam sebelum ujian.',
            'Nilai bukan akhir cerita. Yang penting kamu tahu bagian mana yang perlu diulang.',
            'Bertanya bukan tanda tidak pintar. Itu tanda kamu sedang belajar.',
            'Hari ini cukup jadi sedikit lebih baik dari kemarin.',
            'Kerjakan tugasmu sendiri. Mungkin belum sempurna, tapi hasilnya benar-benar milikmu.',
            'Istirahat juga bagian dari belajar. Tidur cukup, otak lebih siap.',
            'Salah saat latihan itu pelajaran gratis sebelum ujian.',
        ],
        'orang_tua' => [
            'Pertanyaan sederhana "tadi belajar apa?" bisa jadi semangat besar bagi anak.',
            'Hargai usahanya, bukan hanya angkanya.',
            'Anak yang merasa didukung lebih berani mencoba lagi.',
            'Pendampingan kecil yang rutin lebih berarti daripada perhatian besar sesekali.',
            'Nilai yang turun adalah ajakan untuk berbicara, bukan alasan untuk memarahi.',
            'Tempat belajar yang tenang di rumah membantu anak lebih fokus.',
            'Bersama sekolah, Bapak dan Ibu adalah tim terbaik untuk anak.',
        ],
        'guru' => [
            'Ing ngarsa sung tuladha, ing madya mangun karsa, tut wuri handayani.',
            'Nilai yang masuk tepat waktu membantu orang tua mendampingi anak lebih cepat.',
            'Satu kalimat penyemangat dari guru bisa diingat siswa bertahun-tahun.',
            'Setiap kelas berbeda. Data nilai membantu melihat siapa yang perlu didampingi.',
            'Terima kasih sudah hadir untuk siswa hari ini.',
            'Siswa mungkin lupa materinya, tapi akan ingat cara gurunya membuat mereka mau belajar.',
            'Mengajar dengan sabar hari ini adalah menanam untuk masa depan mereka.',
        ],
        'admin' => [
            'Ing ngarsa sung tuladha, ing madya mangun karsa, tut wuri handayani.',
            'Data yang tertib membuat keputusan sekolah lebih tepat.',
            'Dukungan bagi guru adalah dukungan bagi siswa.',
            'Jadwal yang jelas membuat semua pihak lebih siap.',
            'Terima kasih telah menjaga sekolah tetap berjalan dengan baik.',
            'Masalah kecil yang ditangani lebih awal tidak sempat menjadi besar.',
            'Sekolah yang baik dibangun dari kebiasaan baik setiap hari.',
        ],
    ];
    $daftar = $motivasi[Auth::user()->role] ?? $motivasi['siswa'];
    // Ganti setiap hari, tetap sama sepanjang hari itu
    $kata = $daftar[today()->dayOfYear % count($daftar)];
@endphp
<section class="sapaan">
    <div class="kawung kawung-lapis" aria-hidden="true"></div>
    <svg class="bunga" viewBox="0 0 100 100" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="3">
        <ellipse cx="50" cy="24" rx="13" ry="22"/>
        <ellipse cx="76" cy="50" rx="22" ry="13"/>
        <ellipse cx="50" cy="76" rx="13" ry="22"/>
        <ellipse cx="24" cy="50" rx="22" ry="13"/>
        <circle cx="50" cy="50" r="4" fill="currentColor" stroke="none"/>
    </svg>
    <div class="isi pr-24 md:pr-48">
        <p class="sapaan-salam">{{ today()->translatedFormat('l, d F Y') }}</p>
        <h1 class="sapaan-judul">{{ $salam }}, {{ $nama }}</h1>
        <p class="sapaan-motivasi"><i class="fa-solid fa-quote-left" aria-hidden="true"></i>{{ $kata }}</p>
    </div>
</section>