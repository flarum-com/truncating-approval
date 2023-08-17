/// <reference types="flarum/@types/translator-icu-rich" />
import Modal, { IInternalModalAttrs } from 'flarum/common/components/Modal';
import Post from 'flarum/common/models/Post';
import type Mithril from 'mithril';
interface RejectContentModalAttrs extends IInternalModalAttrs {
    post: Post;
    onsubmit: (reason: string) => Promise<void>;
}
interface RejectContentModalState {
    reason: string;
}
export declare class RejectContentModal extends Modal<RejectContentModalAttrs, RejectContentModalState> {
    state: {
        reason: any;
        loadingPreview: any;
        preview: any;
    };
    className(): string;
    title(): import("@askvortsov/rich-icu-message-formatter").NestedStringArray;
    content(): JSX.Element;
    onsubmit(e: SubmitEvent): Promise<void>;
    oncreate(vnode: Mithril.VnodeDOM<RejectContentModalAttrs, RejectContentModalState>): void;
    getPreview(): Promise<void>;
}
export {};
