import EventPost from 'flarum/forum/components/EventPost';
export declare class TruncatingApprovalRejectedPost extends EventPost {
    static initAttrs(attrs: any): void;
    icon(): string;
    descriptionKey(): string;
    descriptionData(): {
        reason: any;
    };
}
