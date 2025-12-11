import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
    static targets = ["input"]

    generate() {
        // Случайный 64-бит seed
        const seed = this.random64bit();
        this.inputTarget.value = seed;
        this.element.dispatchEvent(new Event('change', { bubbles: true }));
    }

    random64bit() {
        // Генерация числа в диапазоне 0 ... 2^63-1 (без отрицательных)
        const hi = Math.floor(Math.random() * 0x100000000); // старшие 32 бита
        const lo = Math.floor(Math.random() * 0x100000000); // младшие 32 бита
        return (BigInt(hi) << 32n) | BigInt(lo);
    }
}
