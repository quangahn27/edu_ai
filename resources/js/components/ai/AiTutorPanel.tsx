import { useHttp } from '@inertiajs/react';
import { useEffect, useRef, useState, type FormEventHandler } from 'react';
import AiMessage, { type TutorMessage } from '@/components/ai/AiMessage';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';

type TutorResponse = {
    conversation_id: number;
    message: TutorMessage;
};

type TutorErrorResponse = {
    message: string;
};

export default function AiTutorPanel({ lessonId }: { lessonId: number }) {
    const [messages, setMessages] = useState<TutorMessage[]>([]);
    const [conversationId, setConversationId] = useState<number | null>(null);
    const [error, setError] = useState<string | null>(null);
    const [loadingHistory, setLoadingHistory] = useState(true);
    const listRef = useRef<HTMLDivElement>(null);

    const { data, setData, post, processing } = useHttp<
        { question: string; conversation_id: number | null },
        TutorResponse
    >('post', `/lessons/${lessonId}/ai/messages`, {
        question: '',
        conversation_id: null,
    });

    useEffect(() => {
        let cancelled = false;

        void fetch(`/lessons/${lessonId}/ai/messages`, {
            headers: { Accept: 'application/json' },
        })
            .then((response) => response.json())
            .then(
                (body: {
                    conversation_id: number | null;
                    messages: TutorMessage[];
                }) => {
                    if (cancelled) return;
                    setConversationId(body.conversation_id);
                    setMessages(body.messages ?? []);
                },
            )
            .catch(() => {
                if (!cancelled) setError('Failed to load the conversation.');
            })
            .finally(() => {
                if (!cancelled) setLoadingHistory(false);
            });

        return () => {
            cancelled = true;
        };
    }, [lessonId]);

    useEffect(() => {
        listRef.current?.scrollTo({ top: listRef.current.scrollHeight });
    }, [messages]);

    useEffect(() => {
        setData('conversation_id', conversationId);
    }, [conversationId, setData]);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        if (!data.question.trim() || processing) {
            return;
        }

        const optimisticMessage: TutorMessage = {
            id: `pending-${Date.now()}`,
            role: 'user',
            content: data.question,
        };

        setMessages((prev) => [...prev, optimisticMessage]);
        setError(null);

        void post(`/lessons/${lessonId}/ai/messages`, {
            onSuccess: (response) => {
                setConversationId(response.conversation_id);
                setMessages((prev) => [...prev, response.message]);
                setData('question', '');
            },
            onError: (errors) => {
                const body = errors as unknown as TutorErrorResponse;
                setError(
                    body?.message ??
                        'Không thể kết nối trợ lý AI lúc này. Vui lòng thử lại.',
                );
            },
        });
    };

    return (
        <div className="flex h-full flex-col">
            <div ref={listRef} className="flex-1 space-y-3 overflow-y-auto p-3">
                {loadingHistory ? (
                    <p className="text-muted-foreground text-sm">
                        Loading conversation...
                    </p>
                ) : messages.length === 0 ? (
                    <p className="text-muted-foreground text-sm">
                        Ask the AI Tutor anything about this lesson.
                    </p>
                ) : (
                    messages.map((message) => (
                        <AiMessage key={message.id} message={message} />
                    ))
                )}
                {processing && (
                    <p className="text-muted-foreground text-sm">Thinking...</p>
                )}
                {error && <p className="text-destructive text-sm">{error}</p>}
            </div>

            <form
                onSubmit={submit}
                className="flex items-end gap-2 border-t p-3"
            >
                <Textarea
                    value={data.question}
                    onChange={(e) => setData('question', e.target.value)}
                    onKeyDown={(e) => {
                        if (e.key === 'Enter' && !e.shiftKey) {
                            e.preventDefault();
                            submit(e as unknown as React.FormEvent);
                        }
                    }}
                    placeholder="Ask a question about this lesson..."
                    rows={2}
                    className="flex-1"
                />
                <Button type="submit" disabled={processing}>
                    Send
                </Button>
            </form>
        </div>
    );
}
