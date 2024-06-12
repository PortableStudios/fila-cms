import { Extension } from "@tiptap/core";
import { Plugin, PluginKey } from "@tiptap/pm/state";

export const EventHandler = Extension.create({
    name: "eventHandler",
    addProseMirrorPlugins() {
        return [
            new Plugin({
                key: new PluginKey("eventHandler"),
                props: {
                    isTransforming: false,
                    proseEditor: null,
                    createLoader: function(){
                        let loaderDiv = document.createElement('div');
                        loaderDiv.classList.add('absolute','w-full','h-full','z-50','hidden');
                        loaderDiv.style.backgroundColor = 'rgba(240,240,240,0.5)';
                        let svgHtml = '<svg fill="black" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" class="animate-spin fi-btn-icon transition duration-75 h-4 w-4 text-gray-400 dark:text-gray-500" wire:target="">';
                        svgHtml += '<path clip-rule="evenodd" d="M12 19C15.866 19 19 15.866 19 12C19 8.13401 15.866 5 12 5C8.13401 5 5 8.13401 5 12C5 15.866 8.13401 19 12 19ZM12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" fill-rule="evenodd" fill="currentColor" opacity="0.2"></path>';
                        svgHtml += '<path d="M2 12C2 6.47715 6.47715 2 12 2V5C8.13401 5 5 8.13401 5 12H2Z" fill="currentColor"></path>';
                        loaderDiv.innerHTML = '<div class="ml-auto mr-auto"><div class="loader">' + svgHtml + '</svg></div></div>';
                        return loaderDiv;
                    },
                    loading: function(isLoading){
                        this.props.getLoader().classList.toggle('hidden', !isLoading);
                    },
                    getLoader: function(){
                        if(!this.props.proseEditor) return null;
                        if(this.props.loader) return this.props.loader;
                        
                        let tiptapParent = this.props.proseEditor.dom.parentElement.parentElement.parentElement;
                        let loader = this.props.createLoader();
                        tiptapParent.insertBefore(loader, tiptapParent.firstChild);
                        this.props.loader = loader;
                        return this.props.loader;
                    },
                    doAjaxPaste: function(html){
                        this.props.loading(true);
                      this.props.isTransforming = true;

                      let me = this;
                      //Make an AJAX request
                      fetch("/purify", {
                        method: "POST",
                        headers: {
                          "Content-Type": "application/json",
                          "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                          html: html
                        })
                      }).then(function(response){
                        return response.text();
                        }).then(function(html){
                            me.props.proseEditor.pasteHTML(html);
                            me.props.isTransforming = false;
                            me.props.loading(false);
                        });    

                    },
                    transformPastedHTML(html, proseEditor) {
                        if(this.props.isTransforming) return html;
                        this.props.proseEditor = proseEditor;
                        let me = this;
                        window.setTimeout(function(){
                            me.props.doAjaxPaste(html);
                        }, 1);
                        return '';
                    },
                },
            }),
        ];
    },
});
