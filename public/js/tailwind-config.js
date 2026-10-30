// Warna utama EduMonitor (hijau toska). Warna "indigo" lama diarahkan ke warna utama
// supaya halaman lama ikut seragam tanpa perlu diubah satu per satu.
const brand = {
    50: '#eef7f5', 100: '#d5ede8', 200: '#acdad2', 300: '#78bfb3', 400: '#479f92',
    500: '#2a8477', 600: '#1f6b61', 700: '#1c5750', 800: '#1a4742', 900: '#183b37',
};

tailwind.config = {
    theme: {
        extend: {
            colors: { brand, indigo: brand, blue: brand },
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', 'system-ui', 'sans-serif'],
            },
        },
    },
};