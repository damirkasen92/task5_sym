import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
    static targets = ["language", "seed", "likes"];

    update() {
        const frame = document.getElementById("data-frame");
        const url = new URL(frame.src);

        let lang = this.languageTarget.value;

        url.searchParams.set(this.seedTarget.dataset.filtersTarget, this.seedTarget.value);
        url.searchParams.set(this.likesTarget.dataset.filtersTarget, this.likesTarget.value);

        frame.src = `/${lang}/content/?${url.searchParams.toString()}`; //  '/' + lang +  + url.searchParams.toString();
    }
}

