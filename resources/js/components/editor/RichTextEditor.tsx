import { CKEditor } from '@ckeditor/ckeditor5-react';
import {
    BlockQuote,
    Bold,
    ClassicEditor,
    CodeBlock,
    Essentials,
    Heading,
    Italic,
    Link,
    List,
    Paragraph,
    Table,
    TableToolbar,
    Underline,
    Undo,
} from 'ckeditor5';
import 'ckeditor5/ckeditor5.css';

interface RichTextEditorProps {
    value: string;
    onChange: (value: string) => void;
    disabled?: boolean;
    placeholder?: string;
}

export default function RichTextEditor({
    value,
    onChange,
    disabled = false,
    placeholder,
}: RichTextEditorProps) {
    return (
        <div className="rich-text-editor border-input [&_.ck.ck-toolbar]:border-input rounded-md border [&_.ck.ck-content]:min-h-40 [&_.ck.ck-content]:rounded-b-md [&_.ck.ck-content]:border-0 [&_.ck.ck-toolbar]:rounded-t-md [&_.ck.ck-toolbar]:border-0 [&_.ck.ck-toolbar]:border-b">
            <CKEditor
                editor={ClassicEditor}
                data={value}
                disabled={disabled}
                config={{
                    licenseKey: 'GPL',
                    placeholder,
                    plugins: [
                        Essentials,
                        Paragraph,
                        Heading,
                        Bold,
                        Italic,
                        Underline,
                        List,
                        BlockQuote,
                        Link,
                        CodeBlock,
                        Table,
                        TableToolbar,
                        Undo,
                    ],
                    toolbar: [
                        'heading',
                        '|',
                        'bold',
                        'italic',
                        'underline',
                        '|',
                        'bulletedList',
                        'numberedList',
                        '|',
                        'blockQuote',
                        'link',
                        'codeBlock',
                        'insertTable',
                        '|',
                        'undo',
                        'redo',
                    ],
                    table: {
                        contentToolbar: [
                            'tableColumn',
                            'tableRow',
                            'mergeTableCells',
                        ],
                    },
                }}
                onChange={(_event, editor) => onChange(editor.getData())}
            />
        </div>
    );
}
