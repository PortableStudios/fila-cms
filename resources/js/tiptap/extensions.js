import CharacterCount from "@tiptap/extension-character-count";
import { EventHandler } from "./eventHandler";

window.TiptapEditorExtensions = {
    characterCount: [CharacterCount],
    eventHandler: [EventHandler],
};
