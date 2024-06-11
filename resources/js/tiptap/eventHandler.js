import { Extension } from "@tiptap/core";
import { Plugin, PluginKey } from "@tiptap/pm/state";

console.log("Loading eventHandler");
export const EventHandler = Extension.create({
    name: "eventHandler",

    addProseMirrorPlugins() {
        return [
            new Plugin({
                key: new PluginKey("eventHandler"),
                props: {
                    transformPastedHTML(html) {
                        console.log("handlePasteHTML");
                        console.log(html);
                        return html.replace(/Foo/i, "Bar");
                    },
                },
            }),
        ];
    },
});
