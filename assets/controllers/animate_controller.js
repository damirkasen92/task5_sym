import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
    connect() {
        this.element.classList.add("fade-enter")
        requestAnimationFrame(() => {
            this.element.classList.add("fade-enter-active")
        })
    }
}
