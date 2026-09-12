import { cn } from '@/lib/utils';

export type TutorMessage = {
    id: number | string;
    role: 'user' | 'assistant';
    content: string;
};

export default function AiMessage({ message }: { message: TutorMessage }) {
    const isUser = message.role === 'user';

    return (
        <div className={cn('flex', isUser ? 'justify-end' : 'justify-start')}>
            <div
                className={cn(
                    'max-w-[85%] rounded-lg px-3 py-2 text-sm whitespace-pre-wrap',
                    isUser
                        ? 'bg-primary text-primary-foreground'
                        : 'bg-muted text-foreground',
                )}
            >
                {message.content}
            </div>
        </div>
    );
}
