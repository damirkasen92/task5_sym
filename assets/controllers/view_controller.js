import {Controller} from "@hotwired/stimulus";
import {setParamDataJson} from "../utils";

export default class extends Controller {
    handle(event) {
        event.preventDefault();

        const newView = event.currentTarget.dataset.view;
        const queryData = readQueryDataJson().toString();
        setParamDataJson('view', newView);

        console.log(queryData);

        // const frame = document.getElementById("data-frame");
        // frame.src = `${url}?${params.toString()}`;
    }
}
