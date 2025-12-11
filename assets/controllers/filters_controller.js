import {Controller} from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["language", "seed", "likes"];

    update() {
        const params = new URLSearchParams(window.location.search);

        let lang = this.languageTarget.value;
        let url = lang === 'en' ? '/content/' : '/' + lang + '/content/';

        params.set("seed", this.seedTarget.value);
        params.set("likes", this.likesTarget.value);
        setParamDataJson('seed', this.seedTarget.value);
        setParamDataJson('likes', this.likesTarget.value);
        setParamDataJson('locale', lang);

        const frame = document.getElementById("data-frame");
        frame.src = `${url}?${params.toString()}`;
    }
}
