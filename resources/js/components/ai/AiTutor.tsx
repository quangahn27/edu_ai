import { Sparkles } from 'lucide-react';
import { useState } from 'react';
import AiTutorPanel from '@/components/ai/AiTutorPanel';
import { Button } from '@/components/ui/button';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';

export default function AiTutor({ lessonId }: { lessonId: number }) {
    const [mobileOpen, setMobileOpen] = useState(false);

    return (
        <>
            {/* Desktop: always-visible side panel */}
            <aside className="hidden w-80 shrink-0 border-l lg:block">
                <div className="border-b p-3">
                    <h2 className="flex items-center gap-2 text-sm font-semibold">
                        <Sparkles className="size-4" />
                        AI Tutor
                    </h2>
                </div>
                <div className="h-[calc(100%-3rem)]">
                    <AiTutorPanel lessonId={lessonId} />
                </div>
            </aside>

            {/* Mobile: floating trigger + drawer */}
            <div className="fixed right-4 bottom-4 z-40 lg:hidden">
                <Button
                    size="icon"
                    className="size-12 rounded-full shadow-lg"
                    onClick={() => setMobileOpen(true)}
                >
                    <Sparkles className="size-5" />
                    <span className="sr-only">Open AI Tutor</span>
                </Button>
            </div>

            <Sheet open={mobileOpen} onOpenChange={setMobileOpen}>
                <SheetContent side="bottom" className="h-[80vh] p-0">
                    <SheetHeader className="border-b">
                        <SheetTitle className="flex items-center gap-2">
                            <Sparkles className="size-4" />
                            AI Tutor
                        </SheetTitle>
                    </SheetHeader>
                    <div className="flex-1 overflow-hidden">
                        <AiTutorPanel lessonId={lessonId} />
                    </div>
                </SheetContent>
            </Sheet>
        </>
    );
}
