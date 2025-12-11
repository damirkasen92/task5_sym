import {Controller} from "@hotwired/stimulus"

export default class extends Controller {
    connect() {
        this.isPlaying = false;
        this.audio = this.element.previousElementSibling;
        this.recordIdx = this.element.dataset.recordIdx;
        this.audio.volume = 0.3;

        this.audio.onended = () => {
            this.isPlaying = false;
            this.element.innerHTML = "▶️ Play Preview";
        }
    }

    click(event) {
        event.preventDefault();

        if (!this.isPlaying) {
            document.querySelectorAll('audio').forEach((el) => {
                if (el.nextElementSibling.classList.contains('playing'))
                    el.nextElementSibling.click();
            });

            if (!this.audio.src || this.audio.src.trim() === "") {
                this.audio.src = this.getSoundStream(this.recordIdx);
            }

            this.audio.play();
            this.isPlaying = true;
            this.element.innerHTML = "⏸ Pause";
            this.element.classList.add("playing");
        } else {
            this.audio.pause();
            this.isPlaying = false;
            this.element.innerHTML = "▶️ Play Preview";
            this.element.classList.remove("playing");
        }
    }

    getSoundStream(recordIdx) {
        const seed = document.getElementById('seed').value;
        const frame = document.getElementById('data-frame');
        const url = new URL(frame.src);
        const lang = document.getElementById('language').value;
        const neededParams = ['seed', 'page'];

        url.searchParams.forEach((value, key) => {
            if (!neededParams.includes(key)) {
                url.searchParams.delete(key);
            }
        });

        url.searchParams.set('record_index', recordIdx);

        return `${url.protocol}//${url.host}/${lang}/wav/${seed}?${url.searchParams.toString()}`;
    }
}
