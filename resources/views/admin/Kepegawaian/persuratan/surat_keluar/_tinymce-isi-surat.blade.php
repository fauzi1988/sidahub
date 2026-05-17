@push('styles')
<style>
   .tox-tinymce { border-radius: 4px !important; }
   .isi-surat-html table { width: 100%; border-collapse: collapse; }
   .isi-surat-html th, .isi-surat-html td { border: 1px solid #dee2e6; padding: 6px 8px; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tinymce@7.6.1/tinymce.min.js" referrerpolicy="origin"></script>
<script>
(function () {
   const editorId = 'isi_surat';
   const templates = @json($isiTemplates ?? []);

   tinymce.init({
      selector: '#' + editorId,
      height: 420,
      menubar: false,
      branding: false,
      promotion: false,
      license_key: 'gpl',
      plugins: 'lists table link autolink paste code',
      toolbar: 'undo redo | blocks | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist | table | removeformat',
      block_formats: 'Paragraph=p; Heading 3=h3; Heading 4=h4',
      paste_as_text: false,
      paste_data_images: false,
      table_toolbar: 'tableprops tabledelete | tableinsertrowbefore tableinsertrowafter tabledeleterow | tableinsertcolbefore tableinsertcolafter tabledeletecol',
      content_style: 'body { font-family: "Times New Roman", serif; font-size: 12pt; line-height: 1.45; } table { border-collapse: collapse; width: 100%; } td, th { border: 1px solid #000; padding: 4px 8px; }',
      setup: function (editor) {
         editor.on('change keyup', function () {
            editor.save();
         });
      },
   });

   function getEditor() {
      return tinymce.get(editorId);
   }

   function getPlainText() {
      const ed = getEditor();
      return ed ? ed.getContent({ format: 'text' }).trim() : (document.getElementById(editorId)?.value || '').trim();
   }

   function setContent(html) {
      const ed = getEditor();
      if (ed) {
         ed.setContent(html || '');
      } else {
         document.getElementById(editorId).value = html || '';
      }
   }

   document.querySelector('form')?.addEventListener('submit', function (e) {
      tinymce.triggerSave();
      if (getPlainText().length < 20) {
         e.preventDefault();
         alert('Isi surat minimal 20 karakter teks.');
         getEditor()?.focus();
      }
   });

   document.getElementById('btn-apply-template')?.addEventListener('click', function () {
      const jenis = document.getElementById('jenis_surat')?.value;
      if (templates[jenis]) {
         setContent(templates[jenis]);
      }
   });

   document.getElementById('jenis_surat')?.addEventListener('change', function () {
      if (getPlainText().length === 0) {
         document.getElementById('btn-apply-template')?.click();
      }
   });
})();
</script>
@endpush
