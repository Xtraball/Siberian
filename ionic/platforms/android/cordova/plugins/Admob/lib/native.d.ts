import { MobileAd, MobileAdOptions } from './shared';
export declare class ManagedNativeAd extends MobileAd {
}
export default class NativeAd extends MobileAd {
    _init: Promise<void> | null;
    constructor(opts: MobileAdOptions);
    load(): Promise<void>;
}
