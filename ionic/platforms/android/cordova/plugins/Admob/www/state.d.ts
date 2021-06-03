declare type AdId = number;
declare class AdMobState {
    applicationId: undefined | string;
    devMode: boolean;
    platform: string;
    nextId: AdId;
    adUnits: {
        [key: string]: AdId;
    };
    constructor();
    getAdId(adUnitId: string): number;
}
export default AdMobState;
