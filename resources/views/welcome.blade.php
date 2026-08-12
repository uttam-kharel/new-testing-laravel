<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Shubham International Hospital — Coming Soon</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#F6FAF9] font-sans text-[#0B2027] antialiased">
        {{-- ── Header ─────────────────────────────────────────────── --}}
        <header class="sticky top-0 z-40 border-b border-[#0E7C7B]/10 bg-[#F6FAF9]/80 backdrop-blur">
            <nav class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
                <a href="#home" class="flex items-center gap-2.5">
                    <span
                        class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#0E7C7B] text-lg text-white"
                    >
                        +
                    </span>
                    <span class="text-sm font-semibold tracking-tight sm:text-base">
                        Shubham International Hospital
                    </span>
                </a>
                <div class="hidden items-center gap-8 text-sm font-medium text-[#4B6363] sm:flex">
                    <a href="#home" class="transition hover:text-[#0E7C7B]">Home</a>
                    <a href="#coming" class="transition hover:text-[#0E7C7B]">What's Coming</a>
                    <a href="#contact" class="transition hover:text-[#0E7C7B]">Contact</a>
                </div>
                <a
                    href="#contact"
                    class="rounded-lg bg-[#0E7C7B] px-4 py-2 text-sm font-medium text-white transition hover:bg-[#0B6564]"
                >
                    Contact Us
                </a>
            </nav>
        </header>

        {{-- ── Hero ───────────────────────────────────────────────── --}}
        <main id="home" class="relative overflow-hidden">
            <div
                class="pointer-events-none absolute -top-24 right-0 h-96 w-96 rounded-full bg-[#0E7C7B]/10 blur-3xl"
                aria-hidden="true"
            ></div>
            <div
                class="pointer-events-none absolute -bottom-32 -left-24 h-96 w-96 rounded-full bg-[#0E7C7B]/5 blur-3xl"
                aria-hidden="true"
            ></div>

            <section class="relative mx-auto max-w-3xl px-6 pb-20 pt-24 text-center sm:pt-32">
                <span
                    class="inline-flex items-center gap-2 rounded-full border border-[#0E7C7B]/30 bg-[#0E7C7B]/5 px-4 py-2 text-sm font-medium text-[#0E7C7B]"
                >
                    <span class="relative flex h-3 w-3">
                        <span
                            class="absolute inline-flex h-full w-full animate-ping rounded-full bg-[#0E7C7B] opacity-75"
                        ></span>
                        <span class="relative inline-flex h-3 w-3 rounded-full bg-[#0E7C7B]"></span>
                    </span>
                    Coming Soon
                </span>

                <h1 class="mt-6 text-4xl font-semibold tracking-tight sm:text-6xl">
                    Shubham International Hospital
                </h1>
                <p class="mx-auto mt-5 max-w-xl text-lg leading-relaxed text-[#4B6363]">
                    A new digital experience is on its way. Our website will be launching soon,
                    making it easier to explore our services, departments, doctors, and patient
                    resources — all in one place.
                </p>

                <div class="mt-10 flex flex-col items-center justify-center gap-3 sm:flex-row">
                    <a
                        href="mailto:shubhamintlhospital@gmail.com"
                        class="w-full rounded-lg bg-[#0E7C7B] px-6 py-3 font-medium text-white transition hover:bg-[#0B6564] sm:w-auto"
                    >
                        Contact Us
                    </a>
                    <a
                        href="#coming"
                        class="w-full rounded-lg border border-[#0E7C7B]/25 bg-white px-6 py-3 font-medium text-[#0E7C7B] transition hover:bg-[#0E7C7B]/5 sm:w-auto"
                    >
                        What's Coming
                    </a>
                </div>
            </section>

            {{-- ── What's Coming ──────────────────────────────────── --}}
            <section id="coming" class="relative mx-auto max-w-6xl scroll-mt-24 px-6 pb-20">
                <h2 class="text-center text-2xl font-semibold tracking-tight sm:text-3xl">
                    What's Coming
                </h2>
                <p class="mx-auto mt-3 max-w-lg text-center text-[#4B6363]">
                    Everything you need to know about the hospital, thoughtfully organised.
                </p>

                <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-2xl border border-[#0E7C7B]/15 bg-white p-6 text-left transition hover:-translate-y-1 hover:shadow-lg hover:shadow-[#0E7C7B]/10">
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#0E7C7B]/10 text-2xl">🩺</span>
                        <h3 class="mt-4 font-semibold">Services</h3>
                        <p class="mt-1.5 text-sm leading-relaxed text-[#4B6363]">
                            Discover the full range of medical care we offer.
                        </p>
                    </div>
                    <div class="rounded-2xl border border-[#0E7C7B]/15 bg-white p-6 text-left transition hover:-translate-y-1 hover:shadow-lg hover:shadow-[#0E7C7B]/10">
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#0E7C7B]/10 text-2xl">🏥</span>
                        <h3 class="mt-4 font-semibold">Departments</h3>
                        <p class="mt-1.5 text-sm leading-relaxed text-[#4B6363]">
                            Explore our specialised departments and facilities.
                        </p>
                    </div>
                    <div class="rounded-2xl border border-[#0E7C7B]/15 bg-white p-6 text-left transition hover:-translate-y-1 hover:shadow-lg hover:shadow-[#0E7C7B]/10">
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#0E7C7B]/10 text-2xl">👨‍⚕️</span>
                        <h3 class="mt-4 font-semibold">Doctors</h3>
                        <p class="mt-1.5 text-sm leading-relaxed text-[#4B6363]">
                            Meet our doctors and their areas of expertise.
                        </p>
                    </div>
                    <div class="rounded-2xl border border-[#0E7C7B]/15 bg-white p-6 text-left transition hover:-translate-y-1 hover:shadow-lg hover:shadow-[#0E7C7B]/10">
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#0E7C7B]/10 text-2xl">📋</span>
                        <h3 class="mt-4 font-semibold">Patient Resources</h3>
                        <p class="mt-1.5 text-sm leading-relaxed text-[#4B6363]">
                            Helpful guides and information for patients.
                        </p>
                    </div>
                </div>
            </section>

            {{-- ── Contact ────────────────────────────────────────── --}}
            <section id="contact" class="relative mx-auto max-w-6xl scroll-mt-24 px-6 pb-20">
                <div class="rounded-3xl bg-[#0B2027] px-6 py-12 text-center sm:px-12">
                    <h2 class="text-2xl font-semibold tracking-tight text-white sm:text-3xl">
                        Stay in Touch
                    </h2>
                    <p class="mx-auto mt-3 max-w-md text-[#9FB8B8]">
                        Have a question before the website launches? We'd love to hear from you.
                    </p>
                    <a
                        href="mailto:shubhamintlhospital@gmail.com"
                        class="mt-8 inline-flex items-center gap-2 rounded-lg bg-[#0E7C7B] px-6 py-3 font-medium text-white transition hover:bg-[#0B6564]"
                    >
                        shubhamintlhospital@gmail.com
                    </a>
                </div>
            </section>
        </main>

        {{-- ── Footer ─────────────────────────────────────────────── --}}
        <footer class="border-t border-[#0E7C7B]/10 py-8">
            <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-3 px-6 text-sm text-[#4B6363] sm:flex-row">
                <p>© {{ date('Y') }} Shubham International Hospital</p>
                <p>Coming soon — we can't wait to welcome you.</p>
            </div>
        </footer>
    </body>
</html>
