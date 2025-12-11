import {Controller} from "@hotwired/stimulus"

export default class extends Controller {
    switch(event) {
        const frame = document.getElementById("data-frame");
        const view = event.currentTarget.dataset.view;
        const url = new URL(frame.src);

        url.searchParams.set("view", view);
        url.searchParams.delete("page");

        document.querySelectorAll(`[data-view]`)
            .forEach(el => el.classList.remove('active'));
        document.querySelector(`[data-view="${view}"]`).classList.add('active');

        frame.src = url.toString();
    }
}
