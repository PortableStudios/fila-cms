import CharacterCount from "@tiptap/extension-character-count";
import { EventHandler } from "./eventHandler";

console.log("Loading extensions");
window.TiptapEditorExtensions = {
    characterCount: [CharacterCount],
    eventHandler: [EventHandler],
};
