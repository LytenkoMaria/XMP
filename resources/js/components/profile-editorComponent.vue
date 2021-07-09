<template>
    <div class="container rad">
        <ckeditor v-if="show"  v-model="editorData" :config="editorConfig"></ckeditor>
        <div class="rad">
        <button v-if="show" type="button" v-on:click.prevent="getData()" class="mt-3 btn savebtn btn-outline-primary">Save changes</button>
        <button v-if="show" type="button" v-on:click.prevent="previewEditor()" class="mt-3 ml-4 btn preview btn-outline-danger">Preview</button>
        <button v-if="show" type="button" v-on:click.prevent="cancelEditor()" class="mt-3 ml-4 btn cancel btn-outline-danger">Cancel</button>
        </div>
    </div>

</template>

<script>

export default {
    name: "editor",
    props: ['show', 'saveChangeProfile', 'cancel', 'preview'],
    data() {
        return {
            editorData: null,
            editorConfig: {
                toolbar: [
                    { name: 'clipboard', items: [ 'Cut', '-', 'Undo', 'Redo' ] },
                    { name: 'editing', items: [ 'Find', 'Replace', '-', 'SelectAll', '-', 'Scayt' ] },
                    { name: 'forms', items: [ 'Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField' ] },
                    { name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'CopyFormatting', 'RemoveFormat' ] },
                    { name: 'paragraph', items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl', 'Language' ] },
                    { name: 'links', items: [ 'Link', 'Unlink', 'Anchor' ] },
                    { name: 'insert', items: ['HorizontalRule', 'Smiley', 'SpecialChar', 'PageBreak', 'Iframe' ] },
                    { name: 'styles', items: [ 'Styles', 'Format', 'Font', 'FontSize' ] },
                    { name: 'colors', items: [ 'TextColor', 'BGColor' ] },
                    ],

            }
        };
    },

    methods: {
        getData: function () {
            this.saveChangeProfile({
                data: this.editorData
            })
        },
        showText: function () {
           this.editorData = this.lastText;
        },
        getProfile: function () {
            axios.get('/profile/get')
                .then(
                    (response) => {
                        if (response.data.profile[0].text) {
                            this.editorData = response.data.profile[0].text;
                        }
                    },
                    (error) => {
                        console.log(error.message);
                    }
                )
        },
        cancelEditor:  function () {
            this.cancel({
                cancel: false
            })
        },
        previewEditor:  function () {
            this.preview({
                preview: true,
                newText: this.editorData
            })
        }
    },
    created(){
        this.showText();
        this.getProfile();
    }
}
</script>
<style scoped></style>
