export { AdSizeType, Events, NativeActions } from './generated';
/** @internal */
export declare type MobileAdOptions = {
    id?: number;
    adUnitId: string;
    npa?: '1';
};
/** @internal */
export declare class MobileAd<T extends MobileAdOptions = MobileAdOptions> {
    private static allAds;
    private static idCounter;
    readonly id: number;
    protected readonly opts: T;
    constructor(opts: T);
    static getAdById(id: number): MobileAd<MobileAdOptions>;
    private static nextId;
    get adUnitId(): string;
}
export declare enum MaxAdContentRating {
    G = "G",
    MA = "MA",
    PG = "PG",
    T = "T",
    UNSPECIFIED = ""
}
export declare type RequestConfig = {
    maxAdContentRating?: MaxAdContentRating;
    tagForChildDirectedTreatment?: boolean | null;
    tagForUnderAgeOfConsent?: boolean | null;
    testDeviceIds?: string[];
};
export declare const enum Platforms {
    android = "android",
    ios = "ios"
}
export declare enum TrackingAuthorizationStatus {
    notDetermined = 0,
    restricted = 1,
    denied = 2,
    authorized = 3
}
