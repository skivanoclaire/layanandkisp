{{--
    Ikon ⓘ dengan penjelasan. Parameter: $k (kunci di App\Services\KuesionerPortal\Penjelasan).
    Arahkan kursor untuk melihat, klik/ketuk untuk menahan. Posisi fixed dihitung saat dibuka
    agar tidak terpotong tabel yang bisa digulir dan tetap di dalam layar ponsel.
--}}
<span class="relative inline-block align-middle normal-case font-normal"
      x-data="{
          buka: false, tahan: false, gaya: '',
          tampil() {
              const r = this.$refs.ikon.getBoundingClientRect();
              const w = Math.min(320, window.innerWidth - 32);
              const left = Math.min(Math.max(16, r.left + r.width / 2 - w / 2), window.innerWidth - 16 - w);
              const bawah = window.innerHeight - r.bottom > 220;
              this.gaya = `left:${left}px;width:${w}px;` + (bawah ? `top:${r.bottom + 6}px` : `bottom:${window.innerHeight - r.top + 6}px`);
              this.buka = true;
          },
          tutup() { this.buka = false; this.tahan = false; },
      }"
      @mouseenter="tampil()" @mouseleave="if (!tahan) buka = false"
      @click.outside="tutup()" @keydown.escape.window="tutup()" @scroll.window="tutup()" @resize.window="tutup()">
    <button type="button" x-ref="ikon" aria-label="Penjelasan"
            @click.prevent.stop="tahan = !tahan; tahan ? tampil() : (buka = false)"
            class="inline-flex items-center justify-center w-4 h-4 ml-1 rounded-full border border-gray-400 text-[10px] leading-none text-gray-500 hover:border-green-600 hover:text-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
        i
    </button>
    <span x-show="buka" x-cloak x-transition.opacity role="tooltip" :style="gaya"
          class="fixed z-50 block p-3 rounded-lg shadow-lg bg-gray-900 text-white text-xs leading-relaxed text-left whitespace-normal">
        {{ \App\Services\KuesionerPortal\Penjelasan::get($k) }}
    </span>
</span>
