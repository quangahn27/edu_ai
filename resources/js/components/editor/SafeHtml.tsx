import { cn } from '@/lib/utils';

interface SafeHtmlProps {
    /** HTML that has already been sanitized server-side (e.g. by RichContentSanitizer). */
    html: string;
    className?: string;
}

/**
 * Renders rich text HTML produced by the CKEditor and sanitized on the server.
 * Never pass unsanitized/user-controlled HTML to this component.
 */
export default function SafeHtml({ html, className }: SafeHtmlProps) {
    return (
        <div
            className={cn(
                'max-w-none text-sm leading-relaxed',
                '[&_h1]:mt-6 [&_h1]:mb-3 [&_h1]:text-2xl [&_h1]:font-bold',
                '[&_h2]:mt-6 [&_h2]:mb-3 [&_h2]:text-xl [&_h2]:font-bold',
                '[&_h3]:mt-4 [&_h3]:mb-2 [&_h3]:text-lg [&_h3]:font-semibold',
                '[&_h4]:mt-4 [&_h4]:mb-2 [&_h4]:font-semibold',
                '[&_p]:my-3',
                '[&_ul]:my-3 [&_ul]:list-disc [&_ul]:pl-6',
                '[&_ol]:my-3 [&_ol]:list-decimal [&_ol]:pl-6',
                '[&_li]:my-1',
                '[&_blockquote]:border-muted-foreground/30 [&_blockquote]:text-muted-foreground [&_blockquote]:my-3 [&_blockquote]:border-l-4 [&_blockquote]:pl-4 [&_blockquote]:italic',
                '[&_a]:text-primary [&_a]:underline [&_a]:underline-offset-2',
                '[&_pre]:bg-muted [&_pre]:my-3 [&_pre]:overflow-x-auto [&_pre]:rounded-md [&_pre]:p-3',
                '[&_code]:font-mono [&_code]:text-[0.9em]',
                '[&_table]:my-3 [&_table]:w-full [&_table]:border-collapse',
                '[&_th]:border-border [&_th]:border [&_th]:p-2 [&_th]:text-left',
                '[&_td]:border-border [&_td]:border [&_td]:p-2',
                className,
            )}
            dangerouslySetInnerHTML={{ __html: html }}
        />
    );
}
