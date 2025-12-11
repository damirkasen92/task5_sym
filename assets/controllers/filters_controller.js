import {Controller} from "@hotwired/stimulus"

export default class extends Controller {
    static targets = ["language", "seed", "likes"];

    connect() {
        const range = document.getElementById('likes');
        const output = document.getElementById('likes-value');

        range.addEventListener('input', () => {
            output.textContent = range.value;
        });
    }

    update() {
        const frame = document.getElementById("data-frame");
        const url = new URL(frame.src);

        let lang = this.languageTarget.value;

        url.searchParams.set("page", 1);
        url.searchParams.set(this.seedTarget.dataset.filtersTarget, this.seedTarget.value);
        url.searchParams.set(this.likesTarget.dataset.filtersTarget, this.likesTarget.value);

        frame.src = `${url.protocol}//${url.host}/${lang}/content/?${url.searchParams.toString()}`;
    }
}

