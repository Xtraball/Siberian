import { AdUnitIDOption, Events, NativeActions } from '@admob-plus/core';
import AdMobState from './state';
export declare type MobileAdOptions = {
    adUnitId: string;
};
export declare class MobileAd {
    private static allAds;
    private _id;
    adUnitId: string;
    constructor({ adUnitId }: MobileAdOptions);
    get id(): number;
}
export { AdUnitIDOption, Events, NativeActions };
export declare const enum Platforms {
    android = "android",
    ios = "ios"
}
export declare const enum TestIds {
    dummy = "test",
    banner_android = "ca-app-pub-3940256099942544/6300978111",
    interstitial_android = "ca-app-pub-3940256099942544/1033173712",
    reward_video_android = "ca-app-pub-3940256099942544/5224354917",
    banner_ios = "ca-app-pub-3940256099942544/2934735716",
    interstitial_ios = "ca-app-pub-3940256099942544/4411468910",
    reward_video_ios = "ca-app-pub-3940256099942544/1712485313"
}
export declare function execAsync(action: NativeActions, args?: any[]): Promise<unknown>;
export declare function fireDocumentEvent(eventName: string, data?: null): void;
export declare function waitEvent(successEvent: string, failEvent?: string): Promise<CustomEvent>;
export declare class AdBase {
    protected state: AdMobState;
    protected testIdForAndroid: string;
    protected testIdForIOS: string;
    constructor(state: AdMobState);
    protected get testAdUnitID(): string;
    protected resolveAdUnitID(adUnitID?: AdUnitIDOption): string;
}
