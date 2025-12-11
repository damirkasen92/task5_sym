import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
    static values = { nextUrl: String };

    connect() {
        this.observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting && this.hasNextUrlValue) {
                    this.loadMore();
                }
            });
        });
        this.observer.observe(this.element.lastElementChild);
    }

    async loadMore() {
        const response = await fetch(this.nextUrlValue, { headers: { "Turbo-Frame": "songs" } });
        const html = await response.text();

        this.element.insertAdjacentHTML("beforeend", html);

        this.observer.disconnect();
        this.observer.observe(this.element.lastElementChild);
    }
}
