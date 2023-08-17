import Component, { ComponentAttrs } from 'flarum/common/Component';
import Stream from 'flarum/common/utils/Stream';
import type Mithril from 'mithril';
import ItemList from 'flarum/common/utils/ItemList';
interface Attrs extends ComponentAttrs {
}
export default class RestrictedBbcodes extends Component<Attrs> {
    private static RESTRICTED_BBCODES_KEY;
    loading: boolean;
    shownErrors: {
        oneOrMoreBlanks: boolean;
    };
    get savedRestrictedBbcodes(): string[];
    get isDirty(): boolean;
    restrictedBbcodes: Stream<string>[];
    view(vnode: Mithril.Vnode<Attrs, this>): JSX.Element;
    oncreate(vnode: Mithril.VnodeDOM<Attrs, this>): void;
    newBbcode(): void;
    validate(): boolean;
    save(): Promise<void>;
    errors(): ItemList<unknown>;
}
export {};
