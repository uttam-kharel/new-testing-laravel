<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Shubham International Hospital — Coming Soon</title>
    @vite (['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-[#F6FAF9] text-[#0B2027]">
    <main class="mx-auto max-w-xl px-6 text-center">
        <span
            class="mt-6 inline-flex items-center gap-2 rounded-full border border-[#0E7C7B]/30 bg-[#0E7C7B]/5 px-4 py-2 text-sm font-medium text-[#0E7C7B]"
        >
            <span class="relative flex h-3 w-3">
                <span
                    class="absolute inline-flex h-full w-full animate-ping rounded-full bg-[#0E7C7B] opacity-75"
                ></span>
                <span class="relative inline-flex h-3 w-3 rounded-full bg-[#0E7C7B]"></span>
            </span>
            Coming Soon
        </span>
        <h1 class="font-display mt-5 text-4xl font-semibold tracking-tight sm:text-5xl">
            Shubham International Hospital
        </h1>
        <p class="mt-4 text-lg text-[#4B6363]">A new digital experience is on its way. Our website will be launching soon, making it easier to explore our services, departments, doctors, and patient resources.</p>
        <div class="mt-10 grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="rounded-xl border border-[#0E7C7B]/15 bg-white px-4 py-5">
                <p class="text-2xl">🩺</p>
                <p class="mt-2 text-sm font-semibold text-[#0B2027]">Services</p>
            </div>
            <div class="rounded-xl border border-[#0E7C7B]/15 bg-white px-4 py-5">
                <p class="text-2xl">🏥</p>
                <p class="mt-2 text-sm font-semibold text-[#0B2027]">Departments</p>
            </div>
            <div class="rounded-xl border border-[#0E7C7B]/15 bg-white px-4 py-5">
                <p class="text-2xl">👨‍⚕️</p>
                <p class="mt-2 text-sm font-semibold text-[#0B2027]">Doctors</p>
            </div>
            <div class="rounded-xl border border-[#0E7C7B]/15 bg-white px-4 py-5">
                <p class="text-2xl">📋</p>
                <p class="mt-2 text-sm font-semibold text-[#0B2027]">Patient Resources</p>
            </div>
        </div>
        <div class="mt-10 flex flex-col items-center justify-center gap-3 sm:flex-row">
            <a
                href="mailto:shubhamintlhospital@gmail.com"
                class="rounded-lg bg-[#0E7C7B] px-6 py-3 font-medium text-white transition hover:bg-[#0B6564] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0E7C7B] focus-visible:ring-offset-2 focus-visible:ring-offset-[#F6FAF9]"
            >
                Contact Us
            </a>
        </div>
    </main>
</body>
</html>
