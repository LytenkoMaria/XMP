<template>
    <div>
        <ckeditor :editor="editor" v-model="editorData" :config="editorConfig"></ckeditor>
        <button v-on:click="emptyEditor()">Empty the editor</button>
        <br><br>
        <h2>Editor display html code </h2>
        <br>
        <div>{{editorData}}</div>
        <h2>Editor display html Result </h2>
        <br>
        <button v-on:click="displayEditorResult()">display result </button>
        <br><br>

        <div id="resultingText"></div>
    </div>
</template>

<script>
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';

export default {

    data() {
        return {

            editor: ClassicEditor,
            editorData: 'ckeditor 5 for laravel and vuejs',
            editorConfig: {
                // The configuration of the editor.
            },


        };
    },

    mounted(){

        console.log('Component mounted.')
    },
    methods: {
        emptyEditor() {
            this.editorData = '';
        },
        displayEditorResult(){
            document.getElementById('resultingText').innerHTML = this.editorData;
        }
    }

}
</script>

<script>
export default {
name: "profile-editorComponent"
}
</script>

<style scoped>

</style>
