import { MobileAd, MobileAdOptions } from './shared';
declare class GenericAd extends MobileAd {
    private _init;
    constructor(opts: MobileAdOptions & {
        type: string;
    });
    isLoaded(): Promise<boolean>;
    load(): Promise<void>;
    show(): Promise<boolean>;
}
export default class AppOpenAd extends GenericAd {
    constructor(opts: MobileAdOptions);
}
export {};
