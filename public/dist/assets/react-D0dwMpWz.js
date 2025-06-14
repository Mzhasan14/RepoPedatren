import{r as fg,P as Oe}from"./vendor-CvYCqVRQ.js";import{p as zs,i as dg}from"./fontawesome-B-3jVuOV.js";function hg(a,r){for(var i=0;i<r.length;i++){const u=r[i];if(typeof u!="string"&&!Array.isArray(u)){for(const s in u)if(s!=="default"&&!(s in a)){const f=Object.getOwnPropertyDescriptor(u,s);f&&Object.defineProperty(a,s,f.get?f:{enumerable:!0,get:()=>u[s]})}}}return Object.freeze(Object.defineProperty(a,Symbol.toStringTag,{value:"Module"}))}function mg(a){return a&&a.__esModule&&Object.prototype.hasOwnProperty.call(a,"default")?a.default:a}var ms={exports:{}},Xr={};/**
 * @license React
 * react-jsx-runtime.production.js
 *
 * Copyright (c) Meta Platforms, Inc. and affiliates.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */var Q0;function pg(){if(Q0)return Xr;Q0=1;var a=Symbol.for("react.transitional.element"),r=Symbol.for("react.fragment");function i(u,s,f){var m=null;if(f!==void 0&&(m=""+f),s.key!==void 0&&(m=""+s.key),"key"in s){f={};for(var v in s)v!=="key"&&(f[v]=s[v])}else f=s;return s=f.ref,{$$typeof:a,type:u,key:m,ref:s!==void 0?s:null,props:f}}return Xr.Fragment=r,Xr.jsx=i,Xr.jsxs=i,Xr}var Z0;function vg(){return Z0||(Z0=1,ms.exports=pg()),ms.exports}var jl=vg(),ps={exports:{}},he={};/**
 * @license React
 * react.production.js
 *
 * Copyright (c) Meta Platforms, Inc. and affiliates.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */var $0;function bg(){if($0)return he;$0=1;var a=Symbol.for("react.transitional.element"),r=Symbol.for("react.portal"),i=Symbol.for("react.fragment"),u=Symbol.for("react.strict_mode"),s=Symbol.for("react.profiler"),f=Symbol.for("react.consumer"),m=Symbol.for("react.context"),v=Symbol.for("react.forward_ref"),h=Symbol.for("react.suspense"),p=Symbol.for("react.memo"),g=Symbol.for("react.lazy"),z=Symbol.iterator;function M(T){return T===null||typeof T!="object"?null:(T=z&&T[z]||T["@@iterator"],typeof T=="function"?T:null)}var w={isMounted:function(){return!1},enqueueForceUpdate:function(){},enqueueReplaceState:function(){},enqueueSetState:function(){}},A=Object.assign,E={};function R(T,Y,ue){this.props=T,this.context=Y,this.refs=E,this.updater=ue||w}R.prototype.isReactComponent={},R.prototype.setState=function(T,Y){if(typeof T!="object"&&typeof T!="function"&&T!=null)throw Error("takes an object of state variables to update or a function which returns an object of state variables.");this.updater.enqueueSetState(this,T,Y,"setState")},R.prototype.forceUpdate=function(T){this.updater.enqueueForceUpdate(this,T,"forceUpdate")};function q(){}q.prototype=R.prototype;function N(T,Y,ue){this.props=T,this.context=Y,this.refs=E,this.updater=ue||w}var V=N.prototype=new q;V.constructor=N,A(V,R.prototype),V.isPureReactComponent=!0;var F=Array.isArray,$={H:null,A:null,T:null,S:null},me=Object.prototype.hasOwnProperty;function pe(T,Y,ue,ee,ne,ye){return ue=ye.ref,{$$typeof:a,type:T,key:Y,ref:ue!==void 0?ue:null,props:ye}}function ve(T,Y){return pe(T.type,Y,void 0,void 0,void 0,T.props)}function G(T){return typeof T=="object"&&T!==null&&T.$$typeof===a}function W(T){var Y={"=":"=0",":":"=2"};return"$"+T.replace(/[=:]/g,function(ue){return Y[ue]})}var ce=/\/+/g;function oe(T,Y){return typeof T=="object"&&T!==null&&T.key!=null?W(""+T.key):Y.toString(36)}function re(){}function ge(T){switch(T.status){case"fulfilled":return T.value;case"rejected":throw T.reason;default:switch(typeof T.status=="string"?T.then(re,re):(T.status="pending",T.then(function(Y){T.status==="pending"&&(T.status="fulfilled",T.value=Y)},function(Y){T.status==="pending"&&(T.status="rejected",T.reason=Y)})),T.status){case"fulfilled":return T.value;case"rejected":throw T.reason}}throw T}function Te(T,Y,ue,ee,ne){var ye=typeof T;(ye==="undefined"||ye==="boolean")&&(T=null);var de=!1;if(T===null)de=!0;else switch(ye){case"bigint":case"string":case"number":de=!0;break;case"object":switch(T.$$typeof){case a:case r:de=!0;break;case g:return de=T._init,Te(de(T._payload),Y,ue,ee,ne)}}if(de)return ne=ne(T),de=ee===""?"."+oe(T,0):ee,F(ne)?(ue="",de!=null&&(ue=de.replace(ce,"$&/")+"/"),Te(ne,Y,ue,"",function(Me){return Me})):ne!=null&&(G(ne)&&(ne=ve(ne,ue+(ne.key==null||T&&T.key===ne.key?"":(""+ne.key).replace(ce,"$&/")+"/")+de)),Y.push(ne)),1;de=0;var $e=ee===""?".":ee+":";if(F(T))for(var Se=0;Se<T.length;Se++)ee=T[Se],ye=$e+oe(ee,Se),de+=Te(ee,Y,ue,ye,ne);else if(Se=M(T),typeof Se=="function")for(T=Se.call(T),Se=0;!(ee=T.next()).done;)ee=ee.value,ye=$e+oe(ee,Se++),de+=Te(ee,Y,ue,ye,ne);else if(ye==="object"){if(typeof T.then=="function")return Te(ge(T),Y,ue,ee,ne);throw Y=String(T),Error("Objects are not valid as a React child (found: "+(Y==="[object Object]"?"object with keys {"+Object.keys(T).join(", ")+"}":Y)+"). If you meant to render a collection of children, use an array instead.")}return de}function Be(T,Y,ue){if(T==null)return T;var ee=[],ne=0;return Te(T,ee,"","",function(ye){return Y.call(ue,ye,ne++)}),ee}function tt(T){if(T._status===-1){var Y=T._result;Y=Y(),Y.then(function(ue){(T._status===0||T._status===-1)&&(T._status=1,T._result=ue)},function(ue){(T._status===0||T._status===-1)&&(T._status=2,T._result=ue)}),T._status===-1&&(T._status=0,T._result=Y)}if(T._status===1)return T._result.default;throw T._result}var Ve=typeof reportError=="function"?reportError:function(T){if(typeof window=="object"&&typeof window.ErrorEvent=="function"){var Y=new window.ErrorEvent("error",{bubbles:!0,cancelable:!0,message:typeof T=="object"&&T!==null&&typeof T.message=="string"?String(T.message):String(T),error:T});if(!window.dispatchEvent(Y))return}else if(typeof process=="object"&&typeof process.emit=="function"){process.emit("uncaughtException",T);return}console.error(T)};function Je(){}return he.Children={map:Be,forEach:function(T,Y,ue){Be(T,function(){Y.apply(this,arguments)},ue)},count:function(T){var Y=0;return Be(T,function(){Y++}),Y},toArray:function(T){return Be(T,function(Y){return Y})||[]},only:function(T){if(!G(T))throw Error("React.Children.only expected to receive a single React element child.");return T}},he.Component=R,he.Fragment=i,he.Profiler=s,he.PureComponent=N,he.StrictMode=u,he.Suspense=h,he.__CLIENT_INTERNALS_DO_NOT_USE_OR_WARN_USERS_THEY_CANNOT_UPGRADE=$,he.act=function(){throw Error("act(...) is not supported in production builds of React.")},he.cache=function(T){return function(){return T.apply(null,arguments)}},he.cloneElement=function(T,Y,ue){if(T==null)throw Error("The argument must be a React element, but you passed "+T+".");var ee=A({},T.props),ne=T.key,ye=void 0;if(Y!=null)for(de in Y.ref!==void 0&&(ye=void 0),Y.key!==void 0&&(ne=""+Y.key),Y)!me.call(Y,de)||de==="key"||de==="__self"||de==="__source"||de==="ref"&&Y.ref===void 0||(ee[de]=Y[de]);var de=arguments.length-2;if(de===1)ee.children=ue;else if(1<de){for(var $e=Array(de),Se=0;Se<de;Se++)$e[Se]=arguments[Se+2];ee.children=$e}return pe(T.type,ne,void 0,void 0,ye,ee)},he.createContext=function(T){return T={$$typeof:m,_currentValue:T,_currentValue2:T,_threadCount:0,Provider:null,Consumer:null},T.Provider=T,T.Consumer={$$typeof:f,_context:T},T},he.createElement=function(T,Y,ue){var ee,ne={},ye=null;if(Y!=null)for(ee in Y.key!==void 0&&(ye=""+Y.key),Y)me.call(Y,ee)&&ee!=="key"&&ee!=="__self"&&ee!=="__source"&&(ne[ee]=Y[ee]);var de=arguments.length-2;if(de===1)ne.children=ue;else if(1<de){for(var $e=Array(de),Se=0;Se<de;Se++)$e[Se]=arguments[Se+2];ne.children=$e}if(T&&T.defaultProps)for(ee in de=T.defaultProps,de)ne[ee]===void 0&&(ne[ee]=de[ee]);return pe(T,ye,void 0,void 0,null,ne)},he.createRef=function(){return{current:null}},he.forwardRef=function(T){return{$$typeof:v,render:T}},he.isValidElement=G,he.lazy=function(T){return{$$typeof:g,_payload:{_status:-1,_result:T},_init:tt}},he.memo=function(T,Y){return{$$typeof:p,type:T,compare:Y===void 0?null:Y}},he.startTransition=function(T){var Y=$.T,ue={};$.T=ue;try{var ee=T(),ne=$.S;ne!==null&&ne(ue,ee),typeof ee=="object"&&ee!==null&&typeof ee.then=="function"&&ee.then(Je,Ve)}catch(ye){Ve(ye)}finally{$.T=Y}},he.unstable_useCacheRefresh=function(){return $.H.useCacheRefresh()},he.use=function(T){return $.H.use(T)},he.useActionState=function(T,Y,ue){return $.H.useActionState(T,Y,ue)},he.useCallback=function(T,Y){return $.H.useCallback(T,Y)},he.useContext=function(T){return $.H.useContext(T)},he.useDebugValue=function(){},he.useDeferredValue=function(T,Y){return $.H.useDeferredValue(T,Y)},he.useEffect=function(T,Y){return $.H.useEffect(T,Y)},he.useId=function(){return $.H.useId()},he.useImperativeHandle=function(T,Y,ue){return $.H.useImperativeHandle(T,Y,ue)},he.useInsertionEffect=function(T,Y){return $.H.useInsertionEffect(T,Y)},he.useLayoutEffect=function(T,Y){return $.H.useLayoutEffect(T,Y)},he.useMemo=function(T,Y){return $.H.useMemo(T,Y)},he.useOptimistic=function(T,Y){return $.H.useOptimistic(T,Y)},he.useReducer=function(T,Y,ue){return $.H.useReducer(T,Y,ue)},he.useRef=function(T){return $.H.useRef(T)},he.useState=function(T){return $.H.useState(T)},he.useSyncExternalStore=function(T,Y,ue){return $.H.useSyncExternalStore(T,Y,ue)},he.useTransition=function(){return $.H.useTransition()},he.version="19.0.0",he}var P0;function Xs(){return P0||(P0=1,ps.exports=bg()),ps.exports}var y=Xs();const X=mg(y),F0=hg({__proto__:null,default:X},[y]);var vs={exports:{}},Gr={},bs={exports:{}},St={};/**
 * @license React
 * react-dom.production.js
 *
 * Copyright (c) Meta Platforms, Inc. and affiliates.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */var K0;function gg(){if(K0)return St;K0=1;var a=Xs();function r(h){var p="https://react.dev/errors/"+h;if(1<arguments.length){p+="?args[]="+encodeURIComponent(arguments[1]);for(var g=2;g<arguments.length;g++)p+="&args[]="+encodeURIComponent(arguments[g])}return"Minified React error #"+h+"; visit "+p+" for the full message or use the non-minified dev environment for full errors and additional helpful warnings."}function i(){}var u={d:{f:i,r:function(){throw Error(r(522))},D:i,C:i,L:i,m:i,X:i,S:i,M:i},p:0,findDOMNode:null},s=Symbol.for("react.portal");function f(h,p,g){var z=3<arguments.length&&arguments[3]!==void 0?arguments[3]:null;return{$$typeof:s,key:z==null?null:""+z,children:h,containerInfo:p,implementation:g}}var m=a.__CLIENT_INTERNALS_DO_NOT_USE_OR_WARN_USERS_THEY_CANNOT_UPGRADE;function v(h,p){if(h==="font")return"";if(typeof p=="string")return p==="use-credentials"?p:""}return St.__DOM_INTERNALS_DO_NOT_USE_OR_WARN_USERS_THEY_CANNOT_UPGRADE=u,St.createPortal=function(h,p){var g=2<arguments.length&&arguments[2]!==void 0?arguments[2]:null;if(!p||p.nodeType!==1&&p.nodeType!==9&&p.nodeType!==11)throw Error(r(299));return f(h,p,null,g)},St.flushSync=function(h){var p=m.T,g=u.p;try{if(m.T=null,u.p=2,h)return h()}finally{m.T=p,u.p=g,u.d.f()}},St.preconnect=function(h,p){typeof h=="string"&&(p?(p=p.crossOrigin,p=typeof p=="string"?p==="use-credentials"?p:"":void 0):p=null,u.d.C(h,p))},St.prefetchDNS=function(h){typeof h=="string"&&u.d.D(h)},St.preinit=function(h,p){if(typeof h=="string"&&p&&typeof p.as=="string"){var g=p.as,z=v(g,p.crossOrigin),M=typeof p.integrity=="string"?p.integrity:void 0,w=typeof p.fetchPriority=="string"?p.fetchPriority:void 0;g==="style"?u.d.S(h,typeof p.precedence=="string"?p.precedence:void 0,{crossOrigin:z,integrity:M,fetchPriority:w}):g==="script"&&u.d.X(h,{crossOrigin:z,integrity:M,fetchPriority:w,nonce:typeof p.nonce=="string"?p.nonce:void 0})}},St.preinitModule=function(h,p){if(typeof h=="string")if(typeof p=="object"&&p!==null){if(p.as==null||p.as==="script"){var g=v(p.as,p.crossOrigin);u.d.M(h,{crossOrigin:g,integrity:typeof p.integrity=="string"?p.integrity:void 0,nonce:typeof p.nonce=="string"?p.nonce:void 0})}}else p==null&&u.d.M(h)},St.preload=function(h,p){if(typeof h=="string"&&typeof p=="object"&&p!==null&&typeof p.as=="string"){var g=p.as,z=v(g,p.crossOrigin);u.d.L(h,g,{crossOrigin:z,integrity:typeof p.integrity=="string"?p.integrity:void 0,nonce:typeof p.nonce=="string"?p.nonce:void 0,type:typeof p.type=="string"?p.type:void 0,fetchPriority:typeof p.fetchPriority=="string"?p.fetchPriority:void 0,referrerPolicy:typeof p.referrerPolicy=="string"?p.referrerPolicy:void 0,imageSrcSet:typeof p.imageSrcSet=="string"?p.imageSrcSet:void 0,imageSizes:typeof p.imageSizes=="string"?p.imageSizes:void 0,media:typeof p.media=="string"?p.media:void 0})}},St.preloadModule=function(h,p){if(typeof h=="string")if(p){var g=v(p.as,p.crossOrigin);u.d.m(h,{as:typeof p.as=="string"&&p.as!=="script"?p.as:void 0,crossOrigin:g,integrity:typeof p.integrity=="string"?p.integrity:void 0})}else u.d.m(h)},St.requestFormReset=function(h){u.d.r(h)},St.unstable_batchedUpdates=function(h,p){return h(p)},St.useFormState=function(h,p,g){return m.H.useFormState(h,p,g)},St.useFormStatus=function(){return m.H.useHostTransitionStatus()},St.version="19.0.0",St}var J0;function Bm(){if(J0)return bs.exports;J0=1;function a(){if(!(typeof __REACT_DEVTOOLS_GLOBAL_HOOK__>"u"||typeof __REACT_DEVTOOLS_GLOBAL_HOOK__.checkDCE!="function"))try{__REACT_DEVTOOLS_GLOBAL_HOOK__.checkDCE(a)}catch(r){console.error(r)}}return a(),bs.exports=gg(),bs.exports}/**
 * @license React
 * react-dom-client.production.js
 *
 * Copyright (c) Meta Platforms, Inc. and affiliates.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */var W0;function yg(){if(W0)return Gr;W0=1;var a=fg(),r=Xs(),i=Bm();function u(e){var t="https://react.dev/errors/"+e;if(1<arguments.length){t+="?args[]="+encodeURIComponent(arguments[1]);for(var n=2;n<arguments.length;n++)t+="&args[]="+encodeURIComponent(arguments[n])}return"Minified React error #"+e+"; visit "+t+" for the full message or use the non-minified dev environment for full errors and additional helpful warnings."}function s(e){return!(!e||e.nodeType!==1&&e.nodeType!==9&&e.nodeType!==11)}var f=Symbol.for("react.element"),m=Symbol.for("react.transitional.element"),v=Symbol.for("react.portal"),h=Symbol.for("react.fragment"),p=Symbol.for("react.strict_mode"),g=Symbol.for("react.profiler"),z=Symbol.for("react.provider"),M=Symbol.for("react.consumer"),w=Symbol.for("react.context"),A=Symbol.for("react.forward_ref"),E=Symbol.for("react.suspense"),R=Symbol.for("react.suspense_list"),q=Symbol.for("react.memo"),N=Symbol.for("react.lazy"),V=Symbol.for("react.offscreen"),F=Symbol.for("react.memo_cache_sentinel"),$=Symbol.iterator;function me(e){return e===null||typeof e!="object"?null:(e=$&&e[$]||e["@@iterator"],typeof e=="function"?e:null)}var pe=Symbol.for("react.client.reference");function ve(e){if(e==null)return null;if(typeof e=="function")return e.$$typeof===pe?null:e.displayName||e.name||null;if(typeof e=="string")return e;switch(e){case h:return"Fragment";case v:return"Portal";case g:return"Profiler";case p:return"StrictMode";case E:return"Suspense";case R:return"SuspenseList"}if(typeof e=="object")switch(e.$$typeof){case w:return(e.displayName||"Context")+".Provider";case M:return(e._context.displayName||"Context")+".Consumer";case A:var t=e.render;return e=e.displayName,e||(e=t.displayName||t.name||"",e=e!==""?"ForwardRef("+e+")":"ForwardRef"),e;case q:return t=e.displayName||null,t!==null?t:ve(e.type)||"Memo";case N:t=e._payload,e=e._init;try{return ve(e(t))}catch{}}return null}var G=r.__CLIENT_INTERNALS_DO_NOT_USE_OR_WARN_USERS_THEY_CANNOT_UPGRADE,W=Object.assign,ce,oe;function re(e){if(ce===void 0)try{throw Error()}catch(n){var t=n.stack.trim().match(/\n( *(at )?)/);ce=t&&t[1]||"",oe=-1<n.stack.indexOf(`
    at`)?" (<anonymous>)":-1<n.stack.indexOf("@")?"@unknown:0:0":""}return`
`+ce+e+oe}var ge=!1;function Te(e,t){if(!e||ge)return"";ge=!0;var n=Error.prepareStackTrace;Error.prepareStackTrace=void 0;try{var l={DetermineComponentFrameRoot:function(){try{if(t){var Q=function(){throw Error()};if(Object.defineProperty(Q.prototype,"props",{set:function(){throw Error()}}),typeof Reflect=="object"&&Reflect.construct){try{Reflect.construct(Q,[])}catch(L){var k=L}Reflect.construct(e,[],Q)}else{try{Q.call()}catch(L){k=L}e.call(Q.prototype)}}else{try{throw Error()}catch(L){k=L}(Q=e())&&typeof Q.catch=="function"&&Q.catch(function(){})}}catch(L){if(L&&k&&typeof L.stack=="string")return[L.stack,k.stack]}return[null,null]}};l.DetermineComponentFrameRoot.displayName="DetermineComponentFrameRoot";var o=Object.getOwnPropertyDescriptor(l.DetermineComponentFrameRoot,"name");o&&o.configurable&&Object.defineProperty(l.DetermineComponentFrameRoot,"name",{value:"DetermineComponentFrameRoot"});var c=l.DetermineComponentFrameRoot(),d=c[0],b=c[1];if(d&&b){var x=d.split(`
`),_=b.split(`
`);for(o=l=0;l<x.length&&!x[l].includes("DetermineComponentFrameRoot");)l++;for(;o<_.length&&!_[o].includes("DetermineComponentFrameRoot");)o++;if(l===x.length||o===_.length)for(l=x.length-1,o=_.length-1;1<=l&&0<=o&&x[l]!==_[o];)o--;for(;1<=l&&0<=o;l--,o--)if(x[l]!==_[o]){if(l!==1||o!==1)do if(l--,o--,0>o||x[l]!==_[o]){var B=`
`+x[l].replace(" at new "," at ");return e.displayName&&B.includes("<anonymous>")&&(B=B.replace("<anonymous>",e.displayName)),B}while(1<=l&&0<=o);break}}}finally{ge=!1,Error.prepareStackTrace=n}return(n=e?e.displayName||e.name:"")?re(n):""}function Be(e){switch(e.tag){case 26:case 27:case 5:return re(e.type);case 16:return re("Lazy");case 13:return re("Suspense");case 19:return re("SuspenseList");case 0:case 15:return e=Te(e.type,!1),e;case 11:return e=Te(e.type.render,!1),e;case 1:return e=Te(e.type,!0),e;default:return""}}function tt(e){try{var t="";do t+=Be(e),e=e.return;while(e);return t}catch(n){return`
Error generating stack: `+n.message+`
`+n.stack}}function Ve(e){var t=e,n=e;if(e.alternate)for(;t.return;)t=t.return;else{e=t;do t=e,(t.flags&4098)!==0&&(n=t.return),e=t.return;while(e)}return t.tag===3?n:null}function Je(e){if(e.tag===13){var t=e.memoizedState;if(t===null&&(e=e.alternate,e!==null&&(t=e.memoizedState)),t!==null)return t.dehydrated}return null}function T(e){if(Ve(e)!==e)throw Error(u(188))}function Y(e){var t=e.alternate;if(!t){if(t=Ve(e),t===null)throw Error(u(188));return t!==e?null:e}for(var n=e,l=t;;){var o=n.return;if(o===null)break;var c=o.alternate;if(c===null){if(l=o.return,l!==null){n=l;continue}break}if(o.child===c.child){for(c=o.child;c;){if(c===n)return T(o),e;if(c===l)return T(o),t;c=c.sibling}throw Error(u(188))}if(n.return!==l.return)n=o,l=c;else{for(var d=!1,b=o.child;b;){if(b===n){d=!0,n=o,l=c;break}if(b===l){d=!0,l=o,n=c;break}b=b.sibling}if(!d){for(b=c.child;b;){if(b===n){d=!0,n=c,l=o;break}if(b===l){d=!0,l=c,n=o;break}b=b.sibling}if(!d)throw Error(u(189))}}if(n.alternate!==l)throw Error(u(190))}if(n.tag!==3)throw Error(u(188));return n.stateNode.current===n?e:t}function ue(e){var t=e.tag;if(t===5||t===26||t===27||t===6)return e;for(e=e.child;e!==null;){if(t=ue(e),t!==null)return t;e=e.sibling}return null}var ee=Array.isArray,ne=i.__DOM_INTERNALS_DO_NOT_USE_OR_WARN_USERS_THEY_CANNOT_UPGRADE,ye={pending:!1,data:null,method:null,action:null},de=[],$e=-1;function Se(e){return{current:e}}function Me(e){0>$e||(e.current=de[$e],de[$e]=null,$e--)}function Ne(e,t){$e++,de[$e]=e.current,e.current=t}var Dt=Se(null),Fn=Se(null),hn=Se(null),Ta=Se(null);function el(e,t){switch(Ne(hn,t),Ne(Fn,e),Ne(Dt,null),e=t.nodeType,e){case 9:case 11:t=(t=t.documentElement)&&(t=t.namespaceURI)?x0(t):0;break;default:if(e=e===8?t.parentNode:t,t=e.tagName,e=e.namespaceURI)e=x0(e),t=S0(e,t);else switch(t){case"svg":t=1;break;case"math":t=2;break;default:t=0}}Me(Dt),Ne(Dt,t)}function En(){Me(Dt),Me(Fn),Me(hn)}function tl(e){e.memoizedState!==null&&Ne(Ta,e);var t=Dt.current,n=S0(t,e.type);t!==n&&(Ne(Fn,e),Ne(Dt,n))}function nl(e){Fn.current===e&&(Me(Dt),Me(Fn)),Ta.current===e&&(Me(Ta),Lr._currentValue=ye)}var $l=Object.prototype.hasOwnProperty,di=a.unstable_scheduleCallback,al=a.unstable_cancelCallback,S=a.unstable_shouldYield,U=a.unstable_requestPaint,H=a.unstable_now,J=a.unstable_getCurrentPriorityLevel,P=a.unstable_ImmediatePriority,Z=a.unstable_UserBlockingPriority,I=a.unstable_NormalPriority,ze=a.unstable_LowPriority,Xe=a.unstable_IdlePriority,ct=a.log,ou=a.unstable_setDisableYieldValue,On=null,mt=null;function uu(e){if(mt&&typeof mt.onCommitFiberRoot=="function")try{mt.onCommitFiberRoot(On,e,void 0,(e.current.flags&128)===128)}catch{}}function tn(e){if(typeof ct=="function"&&ou(e),mt&&typeof mt.setStrictMode=="function")try{mt.setStrictMode(On,e)}catch{}}var Et=Math.clz32?Math.clz32:Wp,hi=Math.log,Jp=Math.LN2;function Wp(e){return e>>>=0,e===0?32:31-(hi(e)/Jp|0)|0}var mi=128,pi=4194304;function _a(e){var t=e&42;if(t!==0)return t;switch(e&-e){case 1:return 1;case 2:return 2;case 4:return 4;case 8:return 8;case 16:return 16;case 32:return 32;case 64:return 64;case 128:case 256:case 512:case 1024:case 2048:case 4096:case 8192:case 16384:case 32768:case 65536:case 131072:case 262144:case 524288:case 1048576:case 2097152:return e&4194176;case 4194304:case 8388608:case 16777216:case 33554432:return e&62914560;case 67108864:return 67108864;case 134217728:return 134217728;case 268435456:return 268435456;case 536870912:return 536870912;case 1073741824:return 0;default:return e}}function vi(e,t){var n=e.pendingLanes;if(n===0)return 0;var l=0,o=e.suspendedLanes,c=e.pingedLanes,d=e.warmLanes;e=e.finishedLanes!==0;var b=n&134217727;return b!==0?(n=b&~o,n!==0?l=_a(n):(c&=b,c!==0?l=_a(c):e||(d=b&~d,d!==0&&(l=_a(d))))):(b=n&~o,b!==0?l=_a(b):c!==0?l=_a(c):e||(d=n&~d,d!==0&&(l=_a(d)))),l===0?0:t!==0&&t!==l&&(t&o)===0&&(o=l&-l,d=t&-t,o>=d||o===32&&(d&4194176)!==0)?t:l}function Pl(e,t){return(e.pendingLanes&~(e.suspendedLanes&~e.pingedLanes)&t)===0}function Ip(e,t){switch(e){case 1:case 2:case 4:case 8:return t+250;case 16:case 32:case 64:case 128:case 256:case 512:case 1024:case 2048:case 4096:case 8192:case 16384:case 32768:case 65536:case 131072:case 262144:case 524288:case 1048576:case 2097152:return t+5e3;case 4194304:case 8388608:case 16777216:case 33554432:return-1;case 67108864:case 134217728:case 268435456:case 536870912:case 1073741824:return-1;default:return-1}}function df(){var e=mi;return mi<<=1,(mi&4194176)===0&&(mi=128),e}function hf(){var e=pi;return pi<<=1,(pi&62914560)===0&&(pi=4194304),e}function cu(e){for(var t=[],n=0;31>n;n++)t.push(e);return t}function Fl(e,t){e.pendingLanes|=t,t!==268435456&&(e.suspendedLanes=0,e.pingedLanes=0,e.warmLanes=0)}function ev(e,t,n,l,o,c){var d=e.pendingLanes;e.pendingLanes=n,e.suspendedLanes=0,e.pingedLanes=0,e.warmLanes=0,e.expiredLanes&=n,e.entangledLanes&=n,e.errorRecoveryDisabledLanes&=n,e.shellSuspendCounter=0;var b=e.entanglements,x=e.expirationTimes,_=e.hiddenUpdates;for(n=d&~n;0<n;){var B=31-Et(n),Q=1<<B;b[B]=0,x[B]=-1;var k=_[B];if(k!==null)for(_[B]=null,B=0;B<k.length;B++){var L=k[B];L!==null&&(L.lane&=-536870913)}n&=~Q}l!==0&&mf(e,l,0),c!==0&&o===0&&e.tag!==0&&(e.suspendedLanes|=c&~(d&~t))}function mf(e,t,n){e.pendingLanes|=t,e.suspendedLanes&=~t;var l=31-Et(t);e.entangledLanes|=t,e.entanglements[l]=e.entanglements[l]|1073741824|n&4194218}function pf(e,t){var n=e.entangledLanes|=t;for(e=e.entanglements;n;){var l=31-Et(n),o=1<<l;o&t|e[l]&t&&(e[l]|=t),n&=~o}}function vf(e){return e&=-e,2<e?8<e?(e&134217727)!==0?32:268435456:8:2}function bf(){var e=ne.p;return e!==0?e:(e=window.event,e===void 0?32:B0(e.type))}function tv(e,t){var n=ne.p;try{return ne.p=e,t()}finally{ne.p=n}}var Kn=Math.random().toString(36).slice(2),yt="__reactFiber$"+Kn,wt="__reactProps$"+Kn,ll="__reactContainer$"+Kn,su="__reactEvents$"+Kn,nv="__reactListeners$"+Kn,av="__reactHandles$"+Kn,gf="__reactResources$"+Kn,Kl="__reactMarker$"+Kn;function fu(e){delete e[yt],delete e[wt],delete e[su],delete e[nv],delete e[av]}function za(e){var t=e[yt];if(t)return t;for(var n=e.parentNode;n;){if(t=n[ll]||n[yt]){if(n=t.alternate,t.child!==null||n!==null&&n.child!==null)for(e=A0(e);e!==null;){if(n=e[yt])return n;e=A0(e)}return t}e=n,n=e.parentNode}return null}function rl(e){if(e=e[yt]||e[ll]){var t=e.tag;if(t===5||t===6||t===13||t===26||t===27||t===3)return e}return null}function Jl(e){var t=e.tag;if(t===5||t===26||t===27||t===6)return e.stateNode;throw Error(u(33))}function il(e){var t=e[gf];return t||(t=e[gf]={hoistableStyles:new Map,hoistableScripts:new Map}),t}function st(e){e[Kl]=!0}var yf=new Set,xf={};function Da(e,t){ol(e,t),ol(e+"Capture",t)}function ol(e,t){for(xf[e]=t,e=0;e<t.length;e++)yf.add(t[e])}var An=!(typeof window>"u"||typeof window.document>"u"||typeof window.document.createElement>"u"),lv=RegExp("^[:A-Z_a-z\\u00C0-\\u00D6\\u00D8-\\u00F6\\u00F8-\\u02FF\\u0370-\\u037D\\u037F-\\u1FFF\\u200C-\\u200D\\u2070-\\u218F\\u2C00-\\u2FEF\\u3001-\\uD7FF\\uF900-\\uFDCF\\uFDF0-\\uFFFD][:A-Z_a-z\\u00C0-\\u00D6\\u00D8-\\u00F6\\u00F8-\\u02FF\\u0370-\\u037D\\u037F-\\u1FFF\\u200C-\\u200D\\u2070-\\u218F\\u2C00-\\u2FEF\\u3001-\\uD7FF\\uF900-\\uFDCF\\uFDF0-\\uFFFD\\-.0-9\\u00B7\\u0300-\\u036F\\u203F-\\u2040]*$"),Sf={},Ef={};function rv(e){return $l.call(Ef,e)?!0:$l.call(Sf,e)?!1:lv.test(e)?Ef[e]=!0:(Sf[e]=!0,!1)}function bi(e,t,n){if(rv(t))if(n===null)e.removeAttribute(t);else{switch(typeof n){case"undefined":case"function":case"symbol":e.removeAttribute(t);return;case"boolean":var l=t.toLowerCase().slice(0,5);if(l!=="data-"&&l!=="aria-"){e.removeAttribute(t);return}}e.setAttribute(t,""+n)}}function gi(e,t,n){if(n===null)e.removeAttribute(t);else{switch(typeof n){case"undefined":case"function":case"symbol":case"boolean":e.removeAttribute(t);return}e.setAttribute(t,""+n)}}function Tn(e,t,n,l){if(l===null)e.removeAttribute(n);else{switch(typeof l){case"undefined":case"function":case"symbol":case"boolean":e.removeAttribute(n);return}e.setAttributeNS(t,n,""+l)}}function Vt(e){switch(typeof e){case"bigint":case"boolean":case"number":case"string":case"undefined":return e;case"object":return e;default:return""}}function Of(e){var t=e.type;return(e=e.nodeName)&&e.toLowerCase()==="input"&&(t==="checkbox"||t==="radio")}function iv(e){var t=Of(e)?"checked":"value",n=Object.getOwnPropertyDescriptor(e.constructor.prototype,t),l=""+e[t];if(!e.hasOwnProperty(t)&&typeof n<"u"&&typeof n.get=="function"&&typeof n.set=="function"){var o=n.get,c=n.set;return Object.defineProperty(e,t,{configurable:!0,get:function(){return o.call(this)},set:function(d){l=""+d,c.call(this,d)}}),Object.defineProperty(e,t,{enumerable:n.enumerable}),{getValue:function(){return l},setValue:function(d){l=""+d},stopTracking:function(){e._valueTracker=null,delete e[t]}}}}function yi(e){e._valueTracker||(e._valueTracker=iv(e))}function Af(e){if(!e)return!1;var t=e._valueTracker;if(!t)return!0;var n=t.getValue(),l="";return e&&(l=Of(e)?e.checked?"true":"false":e.value),e=l,e!==n?(t.setValue(e),!0):!1}function xi(e){if(e=e||(typeof document<"u"?document:void 0),typeof e>"u")return null;try{return e.activeElement||e.body}catch{return e.body}}var ov=/[\n"\\]/g;function jt(e){return e.replace(ov,function(t){return"\\"+t.charCodeAt(0).toString(16)+" "})}function du(e,t,n,l,o,c,d,b){e.name="",d!=null&&typeof d!="function"&&typeof d!="symbol"&&typeof d!="boolean"?e.type=d:e.removeAttribute("type"),t!=null?d==="number"?(t===0&&e.value===""||e.value!=t)&&(e.value=""+Vt(t)):e.value!==""+Vt(t)&&(e.value=""+Vt(t)):d!=="submit"&&d!=="reset"||e.removeAttribute("value"),t!=null?hu(e,d,Vt(t)):n!=null?hu(e,d,Vt(n)):l!=null&&e.removeAttribute("value"),o==null&&c!=null&&(e.defaultChecked=!!c),o!=null&&(e.checked=o&&typeof o!="function"&&typeof o!="symbol"),b!=null&&typeof b!="function"&&typeof b!="symbol"&&typeof b!="boolean"?e.name=""+Vt(b):e.removeAttribute("name")}function Tf(e,t,n,l,o,c,d,b){if(c!=null&&typeof c!="function"&&typeof c!="symbol"&&typeof c!="boolean"&&(e.type=c),t!=null||n!=null){if(!(c!=="submit"&&c!=="reset"||t!=null))return;n=n!=null?""+Vt(n):"",t=t!=null?""+Vt(t):n,b||t===e.value||(e.value=t),e.defaultValue=t}l=l??o,l=typeof l!="function"&&typeof l!="symbol"&&!!l,e.checked=b?e.checked:!!l,e.defaultChecked=!!l,d!=null&&typeof d!="function"&&typeof d!="symbol"&&typeof d!="boolean"&&(e.name=d)}function hu(e,t,n){t==="number"&&xi(e.ownerDocument)===e||e.defaultValue===""+n||(e.defaultValue=""+n)}function ul(e,t,n,l){if(e=e.options,t){t={};for(var o=0;o<n.length;o++)t["$"+n[o]]=!0;for(n=0;n<e.length;n++)o=t.hasOwnProperty("$"+e[n].value),e[n].selected!==o&&(e[n].selected=o),o&&l&&(e[n].defaultSelected=!0)}else{for(n=""+Vt(n),t=null,o=0;o<e.length;o++){if(e[o].value===n){e[o].selected=!0,l&&(e[o].defaultSelected=!0);return}t!==null||e[o].disabled||(t=e[o])}t!==null&&(t.selected=!0)}}function _f(e,t,n){if(t!=null&&(t=""+Vt(t),t!==e.value&&(e.value=t),n==null)){e.defaultValue!==t&&(e.defaultValue=t);return}e.defaultValue=n!=null?""+Vt(n):""}function zf(e,t,n,l){if(t==null){if(l!=null){if(n!=null)throw Error(u(92));if(ee(l)){if(1<l.length)throw Error(u(93));l=l[0]}n=l}n==null&&(n=""),t=n}n=Vt(t),e.defaultValue=n,l=e.textContent,l===n&&l!==""&&l!==null&&(e.value=l)}function cl(e,t){if(t){var n=e.firstChild;if(n&&n===e.lastChild&&n.nodeType===3){n.nodeValue=t;return}}e.textContent=t}var uv=new Set("animationIterationCount aspectRatio borderImageOutset borderImageSlice borderImageWidth boxFlex boxFlexGroup boxOrdinalGroup columnCount columns flex flexGrow flexPositive flexShrink flexNegative flexOrder gridArea gridRow gridRowEnd gridRowSpan gridRowStart gridColumn gridColumnEnd gridColumnSpan gridColumnStart fontWeight lineClamp lineHeight opacity order orphans scale tabSize widows zIndex zoom fillOpacity floodOpacity stopOpacity strokeDasharray strokeDashoffset strokeMiterlimit strokeOpacity strokeWidth MozAnimationIterationCount MozBoxFlex MozBoxFlexGroup MozLineClamp msAnimationIterationCount msFlex msZoom msFlexGrow msFlexNegative msFlexOrder msFlexPositive msFlexShrink msGridColumn msGridColumnSpan msGridRow msGridRowSpan WebkitAnimationIterationCount WebkitBoxFlex WebKitBoxFlexGroup WebkitBoxOrdinalGroup WebkitColumnCount WebkitColumns WebkitFlex WebkitFlexGrow WebkitFlexPositive WebkitFlexShrink WebkitLineClamp".split(" "));function Df(e,t,n){var l=t.indexOf("--")===0;n==null||typeof n=="boolean"||n===""?l?e.setProperty(t,""):t==="float"?e.cssFloat="":e[t]="":l?e.setProperty(t,n):typeof n!="number"||n===0||uv.has(t)?t==="float"?e.cssFloat=n:e[t]=(""+n).trim():e[t]=n+"px"}function wf(e,t,n){if(t!=null&&typeof t!="object")throw Error(u(62));if(e=e.style,n!=null){for(var l in n)!n.hasOwnProperty(l)||t!=null&&t.hasOwnProperty(l)||(l.indexOf("--")===0?e.setProperty(l,""):l==="float"?e.cssFloat="":e[l]="");for(var o in t)l=t[o],t.hasOwnProperty(o)&&n[o]!==l&&Df(e,o,l)}else for(var c in t)t.hasOwnProperty(c)&&Df(e,c,t[c])}function mu(e){if(e.indexOf("-")===-1)return!1;switch(e){case"annotation-xml":case"color-profile":case"font-face":case"font-face-src":case"font-face-uri":case"font-face-format":case"font-face-name":case"missing-glyph":return!1;default:return!0}}var cv=new Map([["acceptCharset","accept-charset"],["htmlFor","for"],["httpEquiv","http-equiv"],["crossOrigin","crossorigin"],["accentHeight","accent-height"],["alignmentBaseline","alignment-baseline"],["arabicForm","arabic-form"],["baselineShift","baseline-shift"],["capHeight","cap-height"],["clipPath","clip-path"],["clipRule","clip-rule"],["colorInterpolation","color-interpolation"],["colorInterpolationFilters","color-interpolation-filters"],["colorProfile","color-profile"],["colorRendering","color-rendering"],["dominantBaseline","dominant-baseline"],["enableBackground","enable-background"],["fillOpacity","fill-opacity"],["fillRule","fill-rule"],["floodColor","flood-color"],["floodOpacity","flood-opacity"],["fontFamily","font-family"],["fontSize","font-size"],["fontSizeAdjust","font-size-adjust"],["fontStretch","font-stretch"],["fontStyle","font-style"],["fontVariant","font-variant"],["fontWeight","font-weight"],["glyphName","glyph-name"],["glyphOrientationHorizontal","glyph-orientation-horizontal"],["glyphOrientationVertical","glyph-orientation-vertical"],["horizAdvX","horiz-adv-x"],["horizOriginX","horiz-origin-x"],["imageRendering","image-rendering"],["letterSpacing","letter-spacing"],["lightingColor","lighting-color"],["markerEnd","marker-end"],["markerMid","marker-mid"],["markerStart","marker-start"],["overlinePosition","overline-position"],["overlineThickness","overline-thickness"],["paintOrder","paint-order"],["panose-1","panose-1"],["pointerEvents","pointer-events"],["renderingIntent","rendering-intent"],["shapeRendering","shape-rendering"],["stopColor","stop-color"],["stopOpacity","stop-opacity"],["strikethroughPosition","strikethrough-position"],["strikethroughThickness","strikethrough-thickness"],["strokeDasharray","stroke-dasharray"],["strokeDashoffset","stroke-dashoffset"],["strokeLinecap","stroke-linecap"],["strokeLinejoin","stroke-linejoin"],["strokeMiterlimit","stroke-miterlimit"],["strokeOpacity","stroke-opacity"],["strokeWidth","stroke-width"],["textAnchor","text-anchor"],["textDecoration","text-decoration"],["textRendering","text-rendering"],["transformOrigin","transform-origin"],["underlinePosition","underline-position"],["underlineThickness","underline-thickness"],["unicodeBidi","unicode-bidi"],["unicodeRange","unicode-range"],["unitsPerEm","units-per-em"],["vAlphabetic","v-alphabetic"],["vHanging","v-hanging"],["vIdeographic","v-ideographic"],["vMathematical","v-mathematical"],["vectorEffect","vector-effect"],["vertAdvY","vert-adv-y"],["vertOriginX","vert-origin-x"],["vertOriginY","vert-origin-y"],["wordSpacing","word-spacing"],["writingMode","writing-mode"],["xmlnsXlink","xmlns:xlink"],["xHeight","x-height"]]),sv=/^[\u0000-\u001F ]*j[\r\n\t]*a[\r\n\t]*v[\r\n\t]*a[\r\n\t]*s[\r\n\t]*c[\r\n\t]*r[\r\n\t]*i[\r\n\t]*p[\r\n\t]*t[\r\n\t]*:/i;function Si(e){return sv.test(""+e)?"javascript:throw new Error('React has blocked a javascript: URL as a security precaution.')":e}var pu=null;function vu(e){return e=e.target||e.srcElement||window,e.correspondingUseElement&&(e=e.correspondingUseElement),e.nodeType===3?e.parentNode:e}var sl=null,fl=null;function Rf(e){var t=rl(e);if(t&&(e=t.stateNode)){var n=e[wt]||null;e:switch(e=t.stateNode,t.type){case"input":if(du(e,n.value,n.defaultValue,n.defaultValue,n.checked,n.defaultChecked,n.type,n.name),t=n.name,n.type==="radio"&&t!=null){for(n=e;n.parentNode;)n=n.parentNode;for(n=n.querySelectorAll('input[name="'+jt(""+t)+'"][type="radio"]'),t=0;t<n.length;t++){var l=n[t];if(l!==e&&l.form===e.form){var o=l[wt]||null;if(!o)throw Error(u(90));du(l,o.value,o.defaultValue,o.defaultValue,o.checked,o.defaultChecked,o.type,o.name)}}for(t=0;t<n.length;t++)l=n[t],l.form===e.form&&Af(l)}break e;case"textarea":_f(e,n.value,n.defaultValue);break e;case"select":t=n.value,t!=null&&ul(e,!!n.multiple,t,!1)}}}var bu=!1;function Mf(e,t,n){if(bu)return e(t,n);bu=!0;try{var l=e(t);return l}finally{if(bu=!1,(sl!==null||fl!==null)&&(lo(),sl&&(t=sl,e=fl,fl=sl=null,Rf(t),e)))for(t=0;t<e.length;t++)Rf(e[t])}}function Wl(e,t){var n=e.stateNode;if(n===null)return null;var l=n[wt]||null;if(l===null)return null;n=l[t];e:switch(t){case"onClick":case"onClickCapture":case"onDoubleClick":case"onDoubleClickCapture":case"onMouseDown":case"onMouseDownCapture":case"onMouseMove":case"onMouseMoveCapture":case"onMouseUp":case"onMouseUpCapture":case"onMouseEnter":(l=!l.disabled)||(e=e.type,l=!(e==="button"||e==="input"||e==="select"||e==="textarea")),e=!l;break e;default:e=!1}if(e)return null;if(n&&typeof n!="function")throw Error(u(231,t,typeof n));return n}var gu=!1;if(An)try{var Il={};Object.defineProperty(Il,"passive",{get:function(){gu=!0}}),window.addEventListener("test",Il,Il),window.removeEventListener("test",Il,Il)}catch{gu=!1}var Jn=null,yu=null,Ei=null;function Cf(){if(Ei)return Ei;var e,t=yu,n=t.length,l,o="value"in Jn?Jn.value:Jn.textContent,c=o.length;for(e=0;e<n&&t[e]===o[e];e++);var d=n-e;for(l=1;l<=d&&t[n-l]===o[c-l];l++);return Ei=o.slice(e,1<l?1-l:void 0)}function Oi(e){var t=e.keyCode;return"charCode"in e?(e=e.charCode,e===0&&t===13&&(e=13)):e=t,e===10&&(e=13),32<=e||e===13?e:0}function Ai(){return!0}function kf(){return!1}function Rt(e){function t(n,l,o,c,d){this._reactName=n,this._targetInst=o,this.type=l,this.nativeEvent=c,this.target=d,this.currentTarget=null;for(var b in e)e.hasOwnProperty(b)&&(n=e[b],this[b]=n?n(c):c[b]);return this.isDefaultPrevented=(c.defaultPrevented!=null?c.defaultPrevented:c.returnValue===!1)?Ai:kf,this.isPropagationStopped=kf,this}return W(t.prototype,{preventDefault:function(){this.defaultPrevented=!0;var n=this.nativeEvent;n&&(n.preventDefault?n.preventDefault():typeof n.returnValue!="unknown"&&(n.returnValue=!1),this.isDefaultPrevented=Ai)},stopPropagation:function(){var n=this.nativeEvent;n&&(n.stopPropagation?n.stopPropagation():typeof n.cancelBubble!="unknown"&&(n.cancelBubble=!0),this.isPropagationStopped=Ai)},persist:function(){},isPersistent:Ai}),t}var wa={eventPhase:0,bubbles:0,cancelable:0,timeStamp:function(e){return e.timeStamp||Date.now()},defaultPrevented:0,isTrusted:0},Ti=Rt(wa),er=W({},wa,{view:0,detail:0}),fv=Rt(er),xu,Su,tr,_i=W({},er,{screenX:0,screenY:0,clientX:0,clientY:0,pageX:0,pageY:0,ctrlKey:0,shiftKey:0,altKey:0,metaKey:0,getModifierState:Ou,button:0,buttons:0,relatedTarget:function(e){return e.relatedTarget===void 0?e.fromElement===e.srcElement?e.toElement:e.fromElement:e.relatedTarget},movementX:function(e){return"movementX"in e?e.movementX:(e!==tr&&(tr&&e.type==="mousemove"?(xu=e.screenX-tr.screenX,Su=e.screenY-tr.screenY):Su=xu=0,tr=e),xu)},movementY:function(e){return"movementY"in e?e.movementY:Su}}),Nf=Rt(_i),dv=W({},_i,{dataTransfer:0}),hv=Rt(dv),mv=W({},er,{relatedTarget:0}),Eu=Rt(mv),pv=W({},wa,{animationName:0,elapsedTime:0,pseudoElement:0}),vv=Rt(pv),bv=W({},wa,{clipboardData:function(e){return"clipboardData"in e?e.clipboardData:window.clipboardData}}),gv=Rt(bv),yv=W({},wa,{data:0}),Uf=Rt(yv),xv={Esc:"Escape",Spacebar:" ",Left:"ArrowLeft",Up:"ArrowUp",Right:"ArrowRight",Down:"ArrowDown",Del:"Delete",Win:"OS",Menu:"ContextMenu",Apps:"ContextMenu",Scroll:"ScrollLock",MozPrintableKey:"Unidentified"},Sv={8:"Backspace",9:"Tab",12:"Clear",13:"Enter",16:"Shift",17:"Control",18:"Alt",19:"Pause",20:"CapsLock",27:"Escape",32:" ",33:"PageUp",34:"PageDown",35:"End",36:"Home",37:"ArrowLeft",38:"ArrowUp",39:"ArrowRight",40:"ArrowDown",45:"Insert",46:"Delete",112:"F1",113:"F2",114:"F3",115:"F4",116:"F5",117:"F6",118:"F7",119:"F8",120:"F9",121:"F10",122:"F11",123:"F12",144:"NumLock",145:"ScrollLock",224:"Meta"},Ev={Alt:"altKey",Control:"ctrlKey",Meta:"metaKey",Shift:"shiftKey"};function Ov(e){var t=this.nativeEvent;return t.getModifierState?t.getModifierState(e):(e=Ev[e])?!!t[e]:!1}function Ou(){return Ov}var Av=W({},er,{key:function(e){if(e.key){var t=xv[e.key]||e.key;if(t!=="Unidentified")return t}return e.type==="keypress"?(e=Oi(e),e===13?"Enter":String.fromCharCode(e)):e.type==="keydown"||e.type==="keyup"?Sv[e.keyCode]||"Unidentified":""},code:0,location:0,ctrlKey:0,shiftKey:0,altKey:0,metaKey:0,repeat:0,locale:0,getModifierState:Ou,charCode:function(e){return e.type==="keypress"?Oi(e):0},keyCode:function(e){return e.type==="keydown"||e.type==="keyup"?e.keyCode:0},which:function(e){return e.type==="keypress"?Oi(e):e.type==="keydown"||e.type==="keyup"?e.keyCode:0}}),Tv=Rt(Av),_v=W({},_i,{pointerId:0,width:0,height:0,pressure:0,tangentialPressure:0,tiltX:0,tiltY:0,twist:0,pointerType:0,isPrimary:0}),qf=Rt(_v),zv=W({},er,{touches:0,targetTouches:0,changedTouches:0,altKey:0,metaKey:0,ctrlKey:0,shiftKey:0,getModifierState:Ou}),Dv=Rt(zv),wv=W({},wa,{propertyName:0,elapsedTime:0,pseudoElement:0}),Rv=Rt(wv),Mv=W({},_i,{deltaX:function(e){return"deltaX"in e?e.deltaX:"wheelDeltaX"in e?-e.wheelDeltaX:0},deltaY:function(e){return"deltaY"in e?e.deltaY:"wheelDeltaY"in e?-e.wheelDeltaY:"wheelDelta"in e?-e.wheelDelta:0},deltaZ:0,deltaMode:0}),Cv=Rt(Mv),kv=W({},wa,{newState:0,oldState:0}),Nv=Rt(kv),Uv=[9,13,27,32],Au=An&&"CompositionEvent"in window,nr=null;An&&"documentMode"in document&&(nr=document.documentMode);var qv=An&&"TextEvent"in window&&!nr,Hf=An&&(!Au||nr&&8<nr&&11>=nr),Lf=" ",Bf=!1;function Vf(e,t){switch(e){case"keyup":return Uv.indexOf(t.keyCode)!==-1;case"keydown":return t.keyCode!==229;case"keypress":case"mousedown":case"focusout":return!0;default:return!1}}function jf(e){return e=e.detail,typeof e=="object"&&"data"in e?e.data:null}var dl=!1;function Hv(e,t){switch(e){case"compositionend":return jf(t);case"keypress":return t.which!==32?null:(Bf=!0,Lf);case"textInput":return e=t.data,e===Lf&&Bf?null:e;default:return null}}function Lv(e,t){if(dl)return e==="compositionend"||!Au&&Vf(e,t)?(e=Cf(),Ei=yu=Jn=null,dl=!1,e):null;switch(e){case"paste":return null;case"keypress":if(!(t.ctrlKey||t.altKey||t.metaKey)||t.ctrlKey&&t.altKey){if(t.char&&1<t.char.length)return t.char;if(t.which)return String.fromCharCode(t.which)}return null;case"compositionend":return Hf&&t.locale!=="ko"?null:t.data;default:return null}}var Bv={color:!0,date:!0,datetime:!0,"datetime-local":!0,email:!0,month:!0,number:!0,password:!0,range:!0,search:!0,tel:!0,text:!0,time:!0,url:!0,week:!0};function Yf(e){var t=e&&e.nodeName&&e.nodeName.toLowerCase();return t==="input"?!!Bv[e.type]:t==="textarea"}function Xf(e,t,n,l){sl?fl?fl.push(l):fl=[l]:sl=l,t=co(t,"onChange"),0<t.length&&(n=new Ti("onChange","change",null,n,l),e.push({event:n,listeners:t}))}var ar=null,lr=null;function Vv(e){p0(e,0)}function zi(e){var t=Jl(e);if(Af(t))return e}function Gf(e,t){if(e==="change")return t}var Qf=!1;if(An){var Tu;if(An){var _u="oninput"in document;if(!_u){var Zf=document.createElement("div");Zf.setAttribute("oninput","return;"),_u=typeof Zf.oninput=="function"}Tu=_u}else Tu=!1;Qf=Tu&&(!document.documentMode||9<document.documentMode)}function $f(){ar&&(ar.detachEvent("onpropertychange",Pf),lr=ar=null)}function Pf(e){if(e.propertyName==="value"&&zi(lr)){var t=[];Xf(t,lr,e,vu(e)),Mf(Vv,t)}}function jv(e,t,n){e==="focusin"?($f(),ar=t,lr=n,ar.attachEvent("onpropertychange",Pf)):e==="focusout"&&$f()}function Yv(e){if(e==="selectionchange"||e==="keyup"||e==="keydown")return zi(lr)}function Xv(e,t){if(e==="click")return zi(t)}function Gv(e,t){if(e==="input"||e==="change")return zi(t)}function Qv(e,t){return e===t&&(e!==0||1/e===1/t)||e!==e&&t!==t}var kt=typeof Object.is=="function"?Object.is:Qv;function rr(e,t){if(kt(e,t))return!0;if(typeof e!="object"||e===null||typeof t!="object"||t===null)return!1;var n=Object.keys(e),l=Object.keys(t);if(n.length!==l.length)return!1;for(l=0;l<n.length;l++){var o=n[l];if(!$l.call(t,o)||!kt(e[o],t[o]))return!1}return!0}function Ff(e){for(;e&&e.firstChild;)e=e.firstChild;return e}function Kf(e,t){var n=Ff(e);e=0;for(var l;n;){if(n.nodeType===3){if(l=e+n.textContent.length,e<=t&&l>=t)return{node:n,offset:t-e};e=l}e:{for(;n;){if(n.nextSibling){n=n.nextSibling;break e}n=n.parentNode}n=void 0}n=Ff(n)}}function Jf(e,t){return e&&t?e===t?!0:e&&e.nodeType===3?!1:t&&t.nodeType===3?Jf(e,t.parentNode):"contains"in e?e.contains(t):e.compareDocumentPosition?!!(e.compareDocumentPosition(t)&16):!1:!1}function Wf(e){e=e!=null&&e.ownerDocument!=null&&e.ownerDocument.defaultView!=null?e.ownerDocument.defaultView:window;for(var t=xi(e.document);t instanceof e.HTMLIFrameElement;){try{var n=typeof t.contentWindow.location.href=="string"}catch{n=!1}if(n)e=t.contentWindow;else break;t=xi(e.document)}return t}function zu(e){var t=e&&e.nodeName&&e.nodeName.toLowerCase();return t&&(t==="input"&&(e.type==="text"||e.type==="search"||e.type==="tel"||e.type==="url"||e.type==="password")||t==="textarea"||e.contentEditable==="true")}function Zv(e,t){var n=Wf(t);t=e.focusedElem;var l=e.selectionRange;if(n!==t&&t&&t.ownerDocument&&Jf(t.ownerDocument.documentElement,t)){if(l!==null&&zu(t)){if(e=l.start,n=l.end,n===void 0&&(n=e),"selectionStart"in t)t.selectionStart=e,t.selectionEnd=Math.min(n,t.value.length);else if(n=(e=t.ownerDocument||document)&&e.defaultView||window,n.getSelection){n=n.getSelection();var o=t.textContent.length,c=Math.min(l.start,o);l=l.end===void 0?c:Math.min(l.end,o),!n.extend&&c>l&&(o=l,l=c,c=o),o=Kf(t,c);var d=Kf(t,l);o&&d&&(n.rangeCount!==1||n.anchorNode!==o.node||n.anchorOffset!==o.offset||n.focusNode!==d.node||n.focusOffset!==d.offset)&&(e=e.createRange(),e.setStart(o.node,o.offset),n.removeAllRanges(),c>l?(n.addRange(e),n.extend(d.node,d.offset)):(e.setEnd(d.node,d.offset),n.addRange(e)))}}for(e=[],n=t;n=n.parentNode;)n.nodeType===1&&e.push({element:n,left:n.scrollLeft,top:n.scrollTop});for(typeof t.focus=="function"&&t.focus(),t=0;t<e.length;t++)n=e[t],n.element.scrollLeft=n.left,n.element.scrollTop=n.top}}var $v=An&&"documentMode"in document&&11>=document.documentMode,hl=null,Du=null,ir=null,wu=!1;function If(e,t,n){var l=n.window===n?n.document:n.nodeType===9?n:n.ownerDocument;wu||hl==null||hl!==xi(l)||(l=hl,"selectionStart"in l&&zu(l)?l={start:l.selectionStart,end:l.selectionEnd}:(l=(l.ownerDocument&&l.ownerDocument.defaultView||window).getSelection(),l={anchorNode:l.anchorNode,anchorOffset:l.anchorOffset,focusNode:l.focusNode,focusOffset:l.focusOffset}),ir&&rr(ir,l)||(ir=l,l=co(Du,"onSelect"),0<l.length&&(t=new Ti("onSelect","select",null,t,n),e.push({event:t,listeners:l}),t.target=hl)))}function Ra(e,t){var n={};return n[e.toLowerCase()]=t.toLowerCase(),n["Webkit"+e]="webkit"+t,n["Moz"+e]="moz"+t,n}var ml={animationend:Ra("Animation","AnimationEnd"),animationiteration:Ra("Animation","AnimationIteration"),animationstart:Ra("Animation","AnimationStart"),transitionrun:Ra("Transition","TransitionRun"),transitionstart:Ra("Transition","TransitionStart"),transitioncancel:Ra("Transition","TransitionCancel"),transitionend:Ra("Transition","TransitionEnd")},Ru={},ed={};An&&(ed=document.createElement("div").style,"AnimationEvent"in window||(delete ml.animationend.animation,delete ml.animationiteration.animation,delete ml.animationstart.animation),"TransitionEvent"in window||delete ml.transitionend.transition);function Ma(e){if(Ru[e])return Ru[e];if(!ml[e])return e;var t=ml[e],n;for(n in t)if(t.hasOwnProperty(n)&&n in ed)return Ru[e]=t[n];return e}var td=Ma("animationend"),nd=Ma("animationiteration"),ad=Ma("animationstart"),Pv=Ma("transitionrun"),Fv=Ma("transitionstart"),Kv=Ma("transitioncancel"),ld=Ma("transitionend"),rd=new Map,id="abort auxClick beforeToggle cancel canPlay canPlayThrough click close contextMenu copy cut drag dragEnd dragEnter dragExit dragLeave dragOver dragStart drop durationChange emptied encrypted ended error gotPointerCapture input invalid keyDown keyPress keyUp load loadedData loadedMetadata loadStart lostPointerCapture mouseDown mouseMove mouseOut mouseOver mouseUp paste pause play playing pointerCancel pointerDown pointerMove pointerOut pointerOver pointerUp progress rateChange reset resize seeked seeking stalled submit suspend timeUpdate touchCancel touchEnd touchStart volumeChange scroll scrollEnd toggle touchMove waiting wheel".split(" ");function nn(e,t){rd.set(e,t),Da(t,[e])}var Yt=[],pl=0,Mu=0;function Di(){for(var e=pl,t=Mu=pl=0;t<e;){var n=Yt[t];Yt[t++]=null;var l=Yt[t];Yt[t++]=null;var o=Yt[t];Yt[t++]=null;var c=Yt[t];if(Yt[t++]=null,l!==null&&o!==null){var d=l.pending;d===null?o.next=o:(o.next=d.next,d.next=o),l.pending=o}c!==0&&od(n,o,c)}}function wi(e,t,n,l){Yt[pl++]=e,Yt[pl++]=t,Yt[pl++]=n,Yt[pl++]=l,Mu|=l,e.lanes|=l,e=e.alternate,e!==null&&(e.lanes|=l)}function Cu(e,t,n,l){return wi(e,t,n,l),Ri(e)}function Wn(e,t){return wi(e,null,null,t),Ri(e)}function od(e,t,n){e.lanes|=n;var l=e.alternate;l!==null&&(l.lanes|=n);for(var o=!1,c=e.return;c!==null;)c.childLanes|=n,l=c.alternate,l!==null&&(l.childLanes|=n),c.tag===22&&(e=c.stateNode,e===null||e._visibility&1||(o=!0)),e=c,c=c.return;o&&t!==null&&e.tag===3&&(c=e.stateNode,o=31-Et(n),c=c.hiddenUpdates,e=c[o],e===null?c[o]=[t]:e.push(t),t.lane=n|536870912)}function Ri(e){if(50<Mr)throw Mr=0,Lc=null,Error(u(185));for(var t=e.return;t!==null;)e=t,t=e.return;return e.tag===3?e.stateNode:null}var vl={},ud=new WeakMap;function Xt(e,t){if(typeof e=="object"&&e!==null){var n=ud.get(e);return n!==void 0?n:(t={value:e,source:t,stack:tt(t)},ud.set(e,t),t)}return{value:e,source:t,stack:tt(t)}}var bl=[],gl=0,Mi=null,Ci=0,Gt=[],Qt=0,Ca=null,_n=1,zn="";function ka(e,t){bl[gl++]=Ci,bl[gl++]=Mi,Mi=e,Ci=t}function cd(e,t,n){Gt[Qt++]=_n,Gt[Qt++]=zn,Gt[Qt++]=Ca,Ca=e;var l=_n;e=zn;var o=32-Et(l)-1;l&=~(1<<o),n+=1;var c=32-Et(t)+o;if(30<c){var d=o-o%5;c=(l&(1<<d)-1).toString(32),l>>=d,o-=d,_n=1<<32-Et(t)+o|n<<o|l,zn=c+e}else _n=1<<c|n<<o|l,zn=e}function ku(e){e.return!==null&&(ka(e,1),cd(e,1,0))}function Nu(e){for(;e===Mi;)Mi=bl[--gl],bl[gl]=null,Ci=bl[--gl],bl[gl]=null;for(;e===Ca;)Ca=Gt[--Qt],Gt[Qt]=null,zn=Gt[--Qt],Gt[Qt]=null,_n=Gt[--Qt],Gt[Qt]=null}var Ot=null,pt=null,we=!1,an=null,mn=!1,Uu=Error(u(519));function Na(e){var t=Error(u(418,""));throw cr(Xt(t,e)),Uu}function sd(e){var t=e.stateNode,n=e.type,l=e.memoizedProps;switch(t[yt]=e,t[wt]=l,n){case"dialog":_e("cancel",t),_e("close",t);break;case"iframe":case"object":case"embed":_e("load",t);break;case"video":case"audio":for(n=0;n<kr.length;n++)_e(kr[n],t);break;case"source":_e("error",t);break;case"img":case"image":case"link":_e("error",t),_e("load",t);break;case"details":_e("toggle",t);break;case"input":_e("invalid",t),Tf(t,l.value,l.defaultValue,l.checked,l.defaultChecked,l.type,l.name,!0),yi(t);break;case"select":_e("invalid",t);break;case"textarea":_e("invalid",t),zf(t,l.value,l.defaultValue,l.children),yi(t)}n=l.children,typeof n!="string"&&typeof n!="number"&&typeof n!="bigint"||t.textContent===""+n||l.suppressHydrationWarning===!0||y0(t.textContent,n)?(l.popover!=null&&(_e("beforetoggle",t),_e("toggle",t)),l.onScroll!=null&&_e("scroll",t),l.onScrollEnd!=null&&_e("scrollend",t),l.onClick!=null&&(t.onclick=so),t=!0):t=!1,t||Na(e)}function fd(e){for(Ot=e.return;Ot;)switch(Ot.tag){case 3:case 27:mn=!0;return;case 5:case 13:mn=!1;return;default:Ot=Ot.return}}function or(e){if(e!==Ot)return!1;if(!we)return fd(e),we=!0,!1;var t=!1,n;if((n=e.tag!==3&&e.tag!==27)&&((n=e.tag===5)&&(n=e.type,n=!(n!=="form"&&n!=="button")||ts(e.type,e.memoizedProps)),n=!n),n&&(t=!0),t&&pt&&Na(e),fd(e),e.tag===13){if(e=e.memoizedState,e=e!==null?e.dehydrated:null,!e)throw Error(u(317));e:{for(e=e.nextSibling,t=0;e;){if(e.nodeType===8)if(n=e.data,n==="/$"){if(t===0){pt=rn(e.nextSibling);break e}t--}else n!=="$"&&n!=="$!"&&n!=="$?"||t++;e=e.nextSibling}pt=null}}else pt=Ot?rn(e.stateNode.nextSibling):null;return!0}function ur(){pt=Ot=null,we=!1}function cr(e){an===null?an=[e]:an.push(e)}var sr=Error(u(460)),dd=Error(u(474)),qu={then:function(){}};function hd(e){return e=e.status,e==="fulfilled"||e==="rejected"}function ki(){}function md(e,t,n){switch(n=e[n],n===void 0?e.push(t):n!==t&&(t.then(ki,ki),t=n),t.status){case"fulfilled":return t.value;case"rejected":throw e=t.reason,e===sr?Error(u(483)):e;default:if(typeof t.status=="string")t.then(ki,ki);else{if(e=He,e!==null&&100<e.shellSuspendCounter)throw Error(u(482));e=t,e.status="pending",e.then(function(l){if(t.status==="pending"){var o=t;o.status="fulfilled",o.value=l}},function(l){if(t.status==="pending"){var o=t;o.status="rejected",o.reason=l}})}switch(t.status){case"fulfilled":return t.value;case"rejected":throw e=t.reason,e===sr?Error(u(483)):e}throw fr=t,sr}}var fr=null;function pd(){if(fr===null)throw Error(u(459));var e=fr;return fr=null,e}var yl=null,dr=0;function Ni(e){var t=dr;return dr+=1,yl===null&&(yl=[]),md(yl,e,t)}function hr(e,t){t=t.props.ref,e.ref=t!==void 0?t:null}function Ui(e,t){throw t.$$typeof===f?Error(u(525)):(e=Object.prototype.toString.call(t),Error(u(31,e==="[object Object]"?"object with keys {"+Object.keys(t).join(", ")+"}":e)))}function vd(e){var t=e._init;return t(e._payload)}function bd(e){function t(D,O){if(e){var C=D.deletions;C===null?(D.deletions=[O],D.flags|=16):C.push(O)}}function n(D,O){if(!e)return null;for(;O!==null;)t(D,O),O=O.sibling;return null}function l(D){for(var O=new Map;D!==null;)D.key!==null?O.set(D.key,D):O.set(D.index,D),D=D.sibling;return O}function o(D,O){return D=sa(D,O),D.index=0,D.sibling=null,D}function c(D,O,C){return D.index=C,e?(C=D.alternate,C!==null?(C=C.index,C<O?(D.flags|=33554434,O):C):(D.flags|=33554434,O)):(D.flags|=1048576,O)}function d(D){return e&&D.alternate===null&&(D.flags|=33554434),D}function b(D,O,C,j){return O===null||O.tag!==6?(O=Rc(C,D.mode,j),O.return=D,O):(O=o(O,C),O.return=D,O)}function x(D,O,C,j){var te=C.type;return te===h?B(D,O,C.props.children,j,C.key):O!==null&&(O.elementType===te||typeof te=="object"&&te!==null&&te.$$typeof===N&&vd(te)===O.type)?(O=o(O,C.props),hr(O,C),O.return=D,O):(O=Ii(C.type,C.key,C.props,null,D.mode,j),hr(O,C),O.return=D,O)}function _(D,O,C,j){return O===null||O.tag!==4||O.stateNode.containerInfo!==C.containerInfo||O.stateNode.implementation!==C.implementation?(O=Mc(C,D.mode,j),O.return=D,O):(O=o(O,C.children||[]),O.return=D,O)}function B(D,O,C,j,te){return O===null||O.tag!==7?(O=Ga(C,D.mode,j,te),O.return=D,O):(O=o(O,C),O.return=D,O)}function Q(D,O,C){if(typeof O=="string"&&O!==""||typeof O=="number"||typeof O=="bigint")return O=Rc(""+O,D.mode,C),O.return=D,O;if(typeof O=="object"&&O!==null){switch(O.$$typeof){case m:return C=Ii(O.type,O.key,O.props,null,D.mode,C),hr(C,O),C.return=D,C;case v:return O=Mc(O,D.mode,C),O.return=D,O;case N:var j=O._init;return O=j(O._payload),Q(D,O,C)}if(ee(O)||me(O))return O=Ga(O,D.mode,C,null),O.return=D,O;if(typeof O.then=="function")return Q(D,Ni(O),C);if(O.$$typeof===w)return Q(D,Ki(D,O),C);Ui(D,O)}return null}function k(D,O,C,j){var te=O!==null?O.key:null;if(typeof C=="string"&&C!==""||typeof C=="number"||typeof C=="bigint")return te!==null?null:b(D,O,""+C,j);if(typeof C=="object"&&C!==null){switch(C.$$typeof){case m:return C.key===te?x(D,O,C,j):null;case v:return C.key===te?_(D,O,C,j):null;case N:return te=C._init,C=te(C._payload),k(D,O,C,j)}if(ee(C)||me(C))return te!==null?null:B(D,O,C,j,null);if(typeof C.then=="function")return k(D,O,Ni(C),j);if(C.$$typeof===w)return k(D,O,Ki(D,C),j);Ui(D,C)}return null}function L(D,O,C,j,te){if(typeof j=="string"&&j!==""||typeof j=="number"||typeof j=="bigint")return D=D.get(C)||null,b(O,D,""+j,te);if(typeof j=="object"&&j!==null){switch(j.$$typeof){case m:return D=D.get(j.key===null?C:j.key)||null,x(O,D,j,te);case v:return D=D.get(j.key===null?C:j.key)||null,_(O,D,j,te);case N:var xe=j._init;return j=xe(j._payload),L(D,O,C,j,te)}if(ee(j)||me(j))return D=D.get(C)||null,B(O,D,j,te,null);if(typeof j.then=="function")return L(D,O,C,Ni(j),te);if(j.$$typeof===w)return L(D,O,C,Ki(O,j),te);Ui(O,j)}return null}function le(D,O,C,j){for(var te=null,xe=null,ie=O,se=O=0,ht=null;ie!==null&&se<C.length;se++){ie.index>se?(ht=ie,ie=null):ht=ie.sibling;var Re=k(D,ie,C[se],j);if(Re===null){ie===null&&(ie=ht);break}e&&ie&&Re.alternate===null&&t(D,ie),O=c(Re,O,se),xe===null?te=Re:xe.sibling=Re,xe=Re,ie=ht}if(se===C.length)return n(D,ie),we&&ka(D,se),te;if(ie===null){for(;se<C.length;se++)ie=Q(D,C[se],j),ie!==null&&(O=c(ie,O,se),xe===null?te=ie:xe.sibling=ie,xe=ie);return we&&ka(D,se),te}for(ie=l(ie);se<C.length;se++)ht=L(ie,D,se,C[se],j),ht!==null&&(e&&ht.alternate!==null&&ie.delete(ht.key===null?se:ht.key),O=c(ht,O,se),xe===null?te=ht:xe.sibling=ht,xe=ht);return e&&ie.forEach(function(ba){return t(D,ba)}),we&&ka(D,se),te}function fe(D,O,C,j){if(C==null)throw Error(u(151));for(var te=null,xe=null,ie=O,se=O=0,ht=null,Re=C.next();ie!==null&&!Re.done;se++,Re=C.next()){ie.index>se?(ht=ie,ie=null):ht=ie.sibling;var ba=k(D,ie,Re.value,j);if(ba===null){ie===null&&(ie=ht);break}e&&ie&&ba.alternate===null&&t(D,ie),O=c(ba,O,se),xe===null?te=ba:xe.sibling=ba,xe=ba,ie=ht}if(Re.done)return n(D,ie),we&&ka(D,se),te;if(ie===null){for(;!Re.done;se++,Re=C.next())Re=Q(D,Re.value,j),Re!==null&&(O=c(Re,O,se),xe===null?te=Re:xe.sibling=Re,xe=Re);return we&&ka(D,se),te}for(ie=l(ie);!Re.done;se++,Re=C.next())Re=L(ie,D,se,Re.value,j),Re!==null&&(e&&Re.alternate!==null&&ie.delete(Re.key===null?se:Re.key),O=c(Re,O,se),xe===null?te=Re:xe.sibling=Re,xe=Re);return e&&ie.forEach(function(sg){return t(D,sg)}),we&&ka(D,se),te}function Ke(D,O,C,j){if(typeof C=="object"&&C!==null&&C.type===h&&C.key===null&&(C=C.props.children),typeof C=="object"&&C!==null){switch(C.$$typeof){case m:e:{for(var te=C.key;O!==null;){if(O.key===te){if(te=C.type,te===h){if(O.tag===7){n(D,O.sibling),j=o(O,C.props.children),j.return=D,D=j;break e}}else if(O.elementType===te||typeof te=="object"&&te!==null&&te.$$typeof===N&&vd(te)===O.type){n(D,O.sibling),j=o(O,C.props),hr(j,C),j.return=D,D=j;break e}n(D,O);break}else t(D,O);O=O.sibling}C.type===h?(j=Ga(C.props.children,D.mode,j,C.key),j.return=D,D=j):(j=Ii(C.type,C.key,C.props,null,D.mode,j),hr(j,C),j.return=D,D=j)}return d(D);case v:e:{for(te=C.key;O!==null;){if(O.key===te)if(O.tag===4&&O.stateNode.containerInfo===C.containerInfo&&O.stateNode.implementation===C.implementation){n(D,O.sibling),j=o(O,C.children||[]),j.return=D,D=j;break e}else{n(D,O);break}else t(D,O);O=O.sibling}j=Mc(C,D.mode,j),j.return=D,D=j}return d(D);case N:return te=C._init,C=te(C._payload),Ke(D,O,C,j)}if(ee(C))return le(D,O,C,j);if(me(C)){if(te=me(C),typeof te!="function")throw Error(u(150));return C=te.call(C),fe(D,O,C,j)}if(typeof C.then=="function")return Ke(D,O,Ni(C),j);if(C.$$typeof===w)return Ke(D,O,Ki(D,C),j);Ui(D,C)}return typeof C=="string"&&C!==""||typeof C=="number"||typeof C=="bigint"?(C=""+C,O!==null&&O.tag===6?(n(D,O.sibling),j=o(O,C),j.return=D,D=j):(n(D,O),j=Rc(C,D.mode,j),j.return=D,D=j),d(D)):n(D,O)}return function(D,O,C,j){try{dr=0;var te=Ke(D,O,C,j);return yl=null,te}catch(ie){if(ie===sr)throw ie;var xe=Ft(29,ie,null,D.mode);return xe.lanes=j,xe.return=D,xe}finally{}}}var Ua=bd(!0),gd=bd(!1),xl=Se(null),qi=Se(0);function yd(e,t){e=Ln,Ne(qi,e),Ne(xl,t),Ln=e|t.baseLanes}function Hu(){Ne(qi,Ln),Ne(xl,xl.current)}function Lu(){Ln=qi.current,Me(xl),Me(qi)}var Zt=Se(null),pn=null;function In(e){var t=e.alternate;Ne(rt,rt.current&1),Ne(Zt,e),pn===null&&(t===null||xl.current!==null||t.memoizedState!==null)&&(pn=e)}function xd(e){if(e.tag===22){if(Ne(rt,rt.current),Ne(Zt,e),pn===null){var t=e.alternate;t!==null&&t.memoizedState!==null&&(pn=e)}}else ea()}function ea(){Ne(rt,rt.current),Ne(Zt,Zt.current)}function Dn(e){Me(Zt),pn===e&&(pn=null),Me(rt)}var rt=Se(0);function Hi(e){for(var t=e;t!==null;){if(t.tag===13){var n=t.memoizedState;if(n!==null&&(n=n.dehydrated,n===null||n.data==="$?"||n.data==="$!"))return t}else if(t.tag===19&&t.memoizedProps.revealOrder!==void 0){if((t.flags&128)!==0)return t}else if(t.child!==null){t.child.return=t,t=t.child;continue}if(t===e)break;for(;t.sibling===null;){if(t.return===null||t.return===e)return null;t=t.return}t.sibling.return=t.return,t=t.sibling}return null}var Jv=typeof AbortController<"u"?AbortController:function(){var e=[],t=this.signal={aborted:!1,addEventListener:function(n,l){e.push(l)}};this.abort=function(){t.aborted=!0,e.forEach(function(n){return n()})}},Wv=a.unstable_scheduleCallback,Iv=a.unstable_NormalPriority,it={$$typeof:w,Consumer:null,Provider:null,_currentValue:null,_currentValue2:null,_threadCount:0};function Bu(){return{controller:new Jv,data:new Map,refCount:0}}function mr(e){e.refCount--,e.refCount===0&&Wv(Iv,function(){e.controller.abort()})}var pr=null,Vu=0,Sl=0,El=null;function eb(e,t){if(pr===null){var n=pr=[];Vu=0,Sl=Zc(),El={status:"pending",value:void 0,then:function(l){n.push(l)}}}return Vu++,t.then(Sd,Sd),t}function Sd(){if(--Vu===0&&pr!==null){El!==null&&(El.status="fulfilled");var e=pr;pr=null,Sl=0,El=null;for(var t=0;t<e.length;t++)(0,e[t])()}}function tb(e,t){var n=[],l={status:"pending",value:null,reason:null,then:function(o){n.push(o)}};return e.then(function(){l.status="fulfilled",l.value=t;for(var o=0;o<n.length;o++)(0,n[o])(t)},function(o){for(l.status="rejected",l.reason=o,o=0;o<n.length;o++)(0,n[o])(void 0)}),l}var Ed=G.S;G.S=function(e,t){typeof t=="object"&&t!==null&&typeof t.then=="function"&&eb(e,t),Ed!==null&&Ed(e,t)};var qa=Se(null);function ju(){var e=qa.current;return e!==null?e:He.pooledCache}function Li(e,t){t===null?Ne(qa,qa.current):Ne(qa,t.pool)}function Od(){var e=ju();return e===null?null:{parent:it._currentValue,pool:e}}var ta=0,be=null,Ce=null,nt=null,Bi=!1,Ol=!1,Ha=!1,Vi=0,vr=0,Al=null,nb=0;function We(){throw Error(u(321))}function Yu(e,t){if(t===null)return!1;for(var n=0;n<t.length&&n<e.length;n++)if(!kt(e[n],t[n]))return!1;return!0}function Xu(e,t,n,l,o,c){return ta=c,be=t,t.memoizedState=null,t.updateQueue=null,t.lanes=0,G.H=e===null||e.memoizedState===null?La:na,Ha=!1,c=n(l,o),Ha=!1,Ol&&(c=Td(t,n,l,o)),Ad(e),c}function Ad(e){G.H=vn;var t=Ce!==null&&Ce.next!==null;if(ta=0,nt=Ce=be=null,Bi=!1,vr=0,Al=null,t)throw Error(u(300));e===null||ft||(e=e.dependencies,e!==null&&Fi(e)&&(ft=!0))}function Td(e,t,n,l){be=e;var o=0;do{if(Ol&&(Al=null),vr=0,Ol=!1,25<=o)throw Error(u(301));if(o+=1,nt=Ce=null,e.updateQueue!=null){var c=e.updateQueue;c.lastEffect=null,c.events=null,c.stores=null,c.memoCache!=null&&(c.memoCache.index=0)}G.H=Ba,c=t(n,l)}while(Ol);return c}function ab(){var e=G.H,t=e.useState()[0];return t=typeof t.then=="function"?br(t):t,e=e.useState()[0],(Ce!==null?Ce.memoizedState:null)!==e&&(be.flags|=1024),t}function Gu(){var e=Vi!==0;return Vi=0,e}function Qu(e,t,n){t.updateQueue=e.updateQueue,t.flags&=-2053,e.lanes&=~n}function Zu(e){if(Bi){for(e=e.memoizedState;e!==null;){var t=e.queue;t!==null&&(t.pending=null),e=e.next}Bi=!1}ta=0,nt=Ce=be=null,Ol=!1,vr=Vi=0,Al=null}function Mt(){var e={memoizedState:null,baseState:null,baseQueue:null,queue:null,next:null};return nt===null?be.memoizedState=nt=e:nt=nt.next=e,nt}function at(){if(Ce===null){var e=be.alternate;e=e!==null?e.memoizedState:null}else e=Ce.next;var t=nt===null?be.memoizedState:nt.next;if(t!==null)nt=t,Ce=e;else{if(e===null)throw be.alternate===null?Error(u(467)):Error(u(310));Ce=e,e={memoizedState:Ce.memoizedState,baseState:Ce.baseState,baseQueue:Ce.baseQueue,queue:Ce.queue,next:null},nt===null?be.memoizedState=nt=e:nt=nt.next=e}return nt}var ji;ji=function(){return{lastEffect:null,events:null,stores:null,memoCache:null}};function br(e){var t=vr;return vr+=1,Al===null&&(Al=[]),e=md(Al,e,t),t=be,(nt===null?t.memoizedState:nt.next)===null&&(t=t.alternate,G.H=t===null||t.memoizedState===null?La:na),e}function Yi(e){if(e!==null&&typeof e=="object"){if(typeof e.then=="function")return br(e);if(e.$$typeof===w)return xt(e)}throw Error(u(438,String(e)))}function $u(e){var t=null,n=be.updateQueue;if(n!==null&&(t=n.memoCache),t==null){var l=be.alternate;l!==null&&(l=l.updateQueue,l!==null&&(l=l.memoCache,l!=null&&(t={data:l.data.map(function(o){return o.slice()}),index:0})))}if(t==null&&(t={data:[],index:0}),n===null&&(n=ji(),be.updateQueue=n),n.memoCache=t,n=t.data[t.index],n===void 0)for(n=t.data[t.index]=Array(e),l=0;l<e;l++)n[l]=F;return t.index++,n}function wn(e,t){return typeof t=="function"?t(e):t}function Xi(e){var t=at();return Pu(t,Ce,e)}function Pu(e,t,n){var l=e.queue;if(l===null)throw Error(u(311));l.lastRenderedReducer=n;var o=e.baseQueue,c=l.pending;if(c!==null){if(o!==null){var d=o.next;o.next=c.next,c.next=d}t.baseQueue=o=c,l.pending=null}if(c=e.baseState,o===null)e.memoizedState=c;else{t=o.next;var b=d=null,x=null,_=t,B=!1;do{var Q=_.lane&-536870913;if(Q!==_.lane?(De&Q)===Q:(ta&Q)===Q){var k=_.revertLane;if(k===0)x!==null&&(x=x.next={lane:0,revertLane:0,action:_.action,hasEagerState:_.hasEagerState,eagerState:_.eagerState,next:null}),Q===Sl&&(B=!0);else if((ta&k)===k){_=_.next,k===Sl&&(B=!0);continue}else Q={lane:0,revertLane:_.revertLane,action:_.action,hasEagerState:_.hasEagerState,eagerState:_.eagerState,next:null},x===null?(b=x=Q,d=c):x=x.next=Q,be.lanes|=k,fa|=k;Q=_.action,Ha&&n(c,Q),c=_.hasEagerState?_.eagerState:n(c,Q)}else k={lane:Q,revertLane:_.revertLane,action:_.action,hasEagerState:_.hasEagerState,eagerState:_.eagerState,next:null},x===null?(b=x=k,d=c):x=x.next=k,be.lanes|=Q,fa|=Q;_=_.next}while(_!==null&&_!==t);if(x===null?d=c:x.next=b,!kt(c,e.memoizedState)&&(ft=!0,B&&(n=El,n!==null)))throw n;e.memoizedState=c,e.baseState=d,e.baseQueue=x,l.lastRenderedState=c}return o===null&&(l.lanes=0),[e.memoizedState,l.dispatch]}function Fu(e){var t=at(),n=t.queue;if(n===null)throw Error(u(311));n.lastRenderedReducer=e;var l=n.dispatch,o=n.pending,c=t.memoizedState;if(o!==null){n.pending=null;var d=o=o.next;do c=e(c,d.action),d=d.next;while(d!==o);kt(c,t.memoizedState)||(ft=!0),t.memoizedState=c,t.baseQueue===null&&(t.baseState=c),n.lastRenderedState=c}return[c,l]}function _d(e,t,n){var l=be,o=at(),c=we;if(c){if(n===void 0)throw Error(u(407));n=n()}else n=t();var d=!kt((Ce||o).memoizedState,n);if(d&&(o.memoizedState=n,ft=!0),o=o.queue,Wu(wd.bind(null,l,o,e),[e]),o.getSnapshot!==t||d||nt!==null&&nt.memoizedState.tag&1){if(l.flags|=2048,Tl(9,Dd.bind(null,l,o,n,t),{destroy:void 0},null),He===null)throw Error(u(349));c||(ta&60)!==0||zd(l,t,n)}return n}function zd(e,t,n){e.flags|=16384,e={getSnapshot:t,value:n},t=be.updateQueue,t===null?(t=ji(),be.updateQueue=t,t.stores=[e]):(n=t.stores,n===null?t.stores=[e]:n.push(e))}function Dd(e,t,n,l){t.value=n,t.getSnapshot=l,Rd(t)&&Md(e)}function wd(e,t,n){return n(function(){Rd(t)&&Md(e)})}function Rd(e){var t=e.getSnapshot;e=e.value;try{var n=t();return!kt(e,n)}catch{return!0}}function Md(e){var t=Wn(e,2);t!==null&&At(t,e,2)}function Ku(e){var t=Mt();if(typeof e=="function"){var n=e;if(e=n(),Ha){tn(!0);try{n()}finally{tn(!1)}}}return t.memoizedState=t.baseState=e,t.queue={pending:null,lanes:0,dispatch:null,lastRenderedReducer:wn,lastRenderedState:e},t}function Cd(e,t,n,l){return e.baseState=n,Pu(e,Ce,typeof l=="function"?l:wn)}function lb(e,t,n,l,o){if(Zi(e))throw Error(u(485));if(e=t.action,e!==null){var c={payload:o,action:e,next:null,isTransition:!0,status:"pending",value:null,reason:null,listeners:[],then:function(d){c.listeners.push(d)}};G.T!==null?n(!0):c.isTransition=!1,l(c),n=t.pending,n===null?(c.next=t.pending=c,kd(t,c)):(c.next=n.next,t.pending=n.next=c)}}function kd(e,t){var n=t.action,l=t.payload,o=e.state;if(t.isTransition){var c=G.T,d={};G.T=d;try{var b=n(o,l),x=G.S;x!==null&&x(d,b),Nd(e,t,b)}catch(_){Ju(e,t,_)}finally{G.T=c}}else try{c=n(o,l),Nd(e,t,c)}catch(_){Ju(e,t,_)}}function Nd(e,t,n){n!==null&&typeof n=="object"&&typeof n.then=="function"?n.then(function(l){Ud(e,t,l)},function(l){return Ju(e,t,l)}):Ud(e,t,n)}function Ud(e,t,n){t.status="fulfilled",t.value=n,qd(t),e.state=n,t=e.pending,t!==null&&(n=t.next,n===t?e.pending=null:(n=n.next,t.next=n,kd(e,n)))}function Ju(e,t,n){var l=e.pending;if(e.pending=null,l!==null){l=l.next;do t.status="rejected",t.reason=n,qd(t),t=t.next;while(t!==l)}e.action=null}function qd(e){e=e.listeners;for(var t=0;t<e.length;t++)(0,e[t])()}function Hd(e,t){return t}function Ld(e,t){if(we){var n=He.formState;if(n!==null){e:{var l=be;if(we){if(pt){t:{for(var o=pt,c=mn;o.nodeType!==8;){if(!c){o=null;break t}if(o=rn(o.nextSibling),o===null){o=null;break t}}c=o.data,o=c==="F!"||c==="F"?o:null}if(o){pt=rn(o.nextSibling),l=o.data==="F!";break e}}Na(l)}l=!1}l&&(t=n[0])}}return n=Mt(),n.memoizedState=n.baseState=t,l={pending:null,lanes:0,dispatch:null,lastRenderedReducer:Hd,lastRenderedState:t},n.queue=l,n=nh.bind(null,be,l),l.dispatch=n,l=Ku(!1),c=ac.bind(null,be,!1,l.queue),l=Mt(),o={state:t,dispatch:null,action:e,pending:null},l.queue=o,n=lb.bind(null,be,o,c,n),o.dispatch=n,l.memoizedState=e,[t,n,!1]}function Bd(e){var t=at();return Vd(t,Ce,e)}function Vd(e,t,n){t=Pu(e,t,Hd)[0],e=Xi(wn)[0],t=typeof t=="object"&&t!==null&&typeof t.then=="function"?br(t):t;var l=at(),o=l.queue,c=o.dispatch;return n!==l.memoizedState&&(be.flags|=2048,Tl(9,rb.bind(null,o,n),{destroy:void 0},null)),[t,c,e]}function rb(e,t){e.action=t}function jd(e){var t=at(),n=Ce;if(n!==null)return Vd(t,n,e);at(),t=t.memoizedState,n=at();var l=n.queue.dispatch;return n.memoizedState=e,[t,l,!1]}function Tl(e,t,n,l){return e={tag:e,create:t,inst:n,deps:l,next:null},t=be.updateQueue,t===null&&(t=ji(),be.updateQueue=t),n=t.lastEffect,n===null?t.lastEffect=e.next=e:(l=n.next,n.next=e,e.next=l,t.lastEffect=e),e}function Yd(){return at().memoizedState}function Gi(e,t,n,l){var o=Mt();be.flags|=e,o.memoizedState=Tl(1|t,n,{destroy:void 0},l===void 0?null:l)}function Qi(e,t,n,l){var o=at();l=l===void 0?null:l;var c=o.memoizedState.inst;Ce!==null&&l!==null&&Yu(l,Ce.memoizedState.deps)?o.memoizedState=Tl(t,n,c,l):(be.flags|=e,o.memoizedState=Tl(1|t,n,c,l))}function Xd(e,t){Gi(8390656,8,e,t)}function Wu(e,t){Qi(2048,8,e,t)}function Gd(e,t){return Qi(4,2,e,t)}function Qd(e,t){return Qi(4,4,e,t)}function Zd(e,t){if(typeof t=="function"){e=e();var n=t(e);return function(){typeof n=="function"?n():t(null)}}if(t!=null)return e=e(),t.current=e,function(){t.current=null}}function $d(e,t,n){n=n!=null?n.concat([e]):null,Qi(4,4,Zd.bind(null,t,e),n)}function Iu(){}function Pd(e,t){var n=at();t=t===void 0?null:t;var l=n.memoizedState;return t!==null&&Yu(t,l[1])?l[0]:(n.memoizedState=[e,t],e)}function Fd(e,t){var n=at();t=t===void 0?null:t;var l=n.memoizedState;if(t!==null&&Yu(t,l[1]))return l[0];if(l=e(),Ha){tn(!0);try{e()}finally{tn(!1)}}return n.memoizedState=[l,t],l}function ec(e,t,n){return n===void 0||(ta&1073741824)!==0?e.memoizedState=t:(e.memoizedState=n,e=Jh(),be.lanes|=e,fa|=e,n)}function Kd(e,t,n,l){return kt(n,t)?n:xl.current!==null?(e=ec(e,n,l),kt(e,t)||(ft=!0),e):(ta&42)===0?(ft=!0,e.memoizedState=n):(e=Jh(),be.lanes|=e,fa|=e,t)}function Jd(e,t,n,l,o){var c=ne.p;ne.p=c!==0&&8>c?c:8;var d=G.T,b={};G.T=b,ac(e,!1,t,n);try{var x=o(),_=G.S;if(_!==null&&_(b,x),x!==null&&typeof x=="object"&&typeof x.then=="function"){var B=tb(x,l);gr(e,t,B,Ht(e))}else gr(e,t,l,Ht(e))}catch(Q){gr(e,t,{then:function(){},status:"rejected",reason:Q},Ht())}finally{ne.p=c,G.T=d}}function ib(){}function tc(e,t,n,l){if(e.tag!==5)throw Error(u(476));var o=Wd(e).queue;Jd(e,o,t,ye,n===null?ib:function(){return Id(e),n(l)})}function Wd(e){var t=e.memoizedState;if(t!==null)return t;t={memoizedState:ye,baseState:ye,baseQueue:null,queue:{pending:null,lanes:0,dispatch:null,lastRenderedReducer:wn,lastRenderedState:ye},next:null};var n={};return t.next={memoizedState:n,baseState:n,baseQueue:null,queue:{pending:null,lanes:0,dispatch:null,lastRenderedReducer:wn,lastRenderedState:n},next:null},e.memoizedState=t,e=e.alternate,e!==null&&(e.memoizedState=t),t}function Id(e){var t=Wd(e).next.queue;gr(e,t,{},Ht())}function nc(){return xt(Lr)}function eh(){return at().memoizedState}function th(){return at().memoizedState}function ob(e){for(var t=e.return;t!==null;){switch(t.tag){case 24:case 3:var n=Ht();e=ra(n);var l=ia(t,e,n);l!==null&&(At(l,t,n),Sr(l,t,n)),t={cache:Bu()},e.payload=t;return}t=t.return}}function ub(e,t,n){var l=Ht();n={lane:l,revertLane:0,action:n,hasEagerState:!1,eagerState:null,next:null},Zi(e)?ah(t,n):(n=Cu(e,t,n,l),n!==null&&(At(n,e,l),lh(n,t,l)))}function nh(e,t,n){var l=Ht();gr(e,t,n,l)}function gr(e,t,n,l){var o={lane:l,revertLane:0,action:n,hasEagerState:!1,eagerState:null,next:null};if(Zi(e))ah(t,o);else{var c=e.alternate;if(e.lanes===0&&(c===null||c.lanes===0)&&(c=t.lastRenderedReducer,c!==null))try{var d=t.lastRenderedState,b=c(d,n);if(o.hasEagerState=!0,o.eagerState=b,kt(b,d))return wi(e,t,o,0),He===null&&Di(),!1}catch{}finally{}if(n=Cu(e,t,o,l),n!==null)return At(n,e,l),lh(n,t,l),!0}return!1}function ac(e,t,n,l){if(l={lane:2,revertLane:Zc(),action:l,hasEagerState:!1,eagerState:null,next:null},Zi(e)){if(t)throw Error(u(479))}else t=Cu(e,n,l,2),t!==null&&At(t,e,2)}function Zi(e){var t=e.alternate;return e===be||t!==null&&t===be}function ah(e,t){Ol=Bi=!0;var n=e.pending;n===null?t.next=t:(t.next=n.next,n.next=t),e.pending=t}function lh(e,t,n){if((n&4194176)!==0){var l=t.lanes;l&=e.pendingLanes,n|=l,t.lanes=n,pf(e,n)}}var vn={readContext:xt,use:Yi,useCallback:We,useContext:We,useEffect:We,useImperativeHandle:We,useLayoutEffect:We,useInsertionEffect:We,useMemo:We,useReducer:We,useRef:We,useState:We,useDebugValue:We,useDeferredValue:We,useTransition:We,useSyncExternalStore:We,useId:We};vn.useCacheRefresh=We,vn.useMemoCache=We,vn.useHostTransitionStatus=We,vn.useFormState=We,vn.useActionState=We,vn.useOptimistic=We;var La={readContext:xt,use:Yi,useCallback:function(e,t){return Mt().memoizedState=[e,t===void 0?null:t],e},useContext:xt,useEffect:Xd,useImperativeHandle:function(e,t,n){n=n!=null?n.concat([e]):null,Gi(4194308,4,Zd.bind(null,t,e),n)},useLayoutEffect:function(e,t){return Gi(4194308,4,e,t)},useInsertionEffect:function(e,t){Gi(4,2,e,t)},useMemo:function(e,t){var n=Mt();t=t===void 0?null:t;var l=e();if(Ha){tn(!0);try{e()}finally{tn(!1)}}return n.memoizedState=[l,t],l},useReducer:function(e,t,n){var l=Mt();if(n!==void 0){var o=n(t);if(Ha){tn(!0);try{n(t)}finally{tn(!1)}}}else o=t;return l.memoizedState=l.baseState=o,e={pending:null,lanes:0,dispatch:null,lastRenderedReducer:e,lastRenderedState:o},l.queue=e,e=e.dispatch=ub.bind(null,be,e),[l.memoizedState,e]},useRef:function(e){var t=Mt();return e={current:e},t.memoizedState=e},useState:function(e){e=Ku(e);var t=e.queue,n=nh.bind(null,be,t);return t.dispatch=n,[e.memoizedState,n]},useDebugValue:Iu,useDeferredValue:function(e,t){var n=Mt();return ec(n,e,t)},useTransition:function(){var e=Ku(!1);return e=Jd.bind(null,be,e.queue,!0,!1),Mt().memoizedState=e,[!1,e]},useSyncExternalStore:function(e,t,n){var l=be,o=Mt();if(we){if(n===void 0)throw Error(u(407));n=n()}else{if(n=t(),He===null)throw Error(u(349));(De&60)!==0||zd(l,t,n)}o.memoizedState=n;var c={value:n,getSnapshot:t};return o.queue=c,Xd(wd.bind(null,l,c,e),[e]),l.flags|=2048,Tl(9,Dd.bind(null,l,c,n,t),{destroy:void 0},null),n},useId:function(){var e=Mt(),t=He.identifierPrefix;if(we){var n=zn,l=_n;n=(l&~(1<<32-Et(l)-1)).toString(32)+n,t=":"+t+"R"+n,n=Vi++,0<n&&(t+="H"+n.toString(32)),t+=":"}else n=nb++,t=":"+t+"r"+n.toString(32)+":";return e.memoizedState=t},useCacheRefresh:function(){return Mt().memoizedState=ob.bind(null,be)}};La.useMemoCache=$u,La.useHostTransitionStatus=nc,La.useFormState=Ld,La.useActionState=Ld,La.useOptimistic=function(e){var t=Mt();t.memoizedState=t.baseState=e;var n={pending:null,lanes:0,dispatch:null,lastRenderedReducer:null,lastRenderedState:null};return t.queue=n,t=ac.bind(null,be,!0,n),n.dispatch=t,[e,t]};var na={readContext:xt,use:Yi,useCallback:Pd,useContext:xt,useEffect:Wu,useImperativeHandle:$d,useInsertionEffect:Gd,useLayoutEffect:Qd,useMemo:Fd,useReducer:Xi,useRef:Yd,useState:function(){return Xi(wn)},useDebugValue:Iu,useDeferredValue:function(e,t){var n=at();return Kd(n,Ce.memoizedState,e,t)},useTransition:function(){var e=Xi(wn)[0],t=at().memoizedState;return[typeof e=="boolean"?e:br(e),t]},useSyncExternalStore:_d,useId:eh};na.useCacheRefresh=th,na.useMemoCache=$u,na.useHostTransitionStatus=nc,na.useFormState=Bd,na.useActionState=Bd,na.useOptimistic=function(e,t){var n=at();return Cd(n,Ce,e,t)};var Ba={readContext:xt,use:Yi,useCallback:Pd,useContext:xt,useEffect:Wu,useImperativeHandle:$d,useInsertionEffect:Gd,useLayoutEffect:Qd,useMemo:Fd,useReducer:Fu,useRef:Yd,useState:function(){return Fu(wn)},useDebugValue:Iu,useDeferredValue:function(e,t){var n=at();return Ce===null?ec(n,e,t):Kd(n,Ce.memoizedState,e,t)},useTransition:function(){var e=Fu(wn)[0],t=at().memoizedState;return[typeof e=="boolean"?e:br(e),t]},useSyncExternalStore:_d,useId:eh};Ba.useCacheRefresh=th,Ba.useMemoCache=$u,Ba.useHostTransitionStatus=nc,Ba.useFormState=jd,Ba.useActionState=jd,Ba.useOptimistic=function(e,t){var n=at();return Ce!==null?Cd(n,Ce,e,t):(n.baseState=e,[e,n.queue.dispatch])};function lc(e,t,n,l){t=e.memoizedState,n=n(l,t),n=n==null?t:W({},t,n),e.memoizedState=n,e.lanes===0&&(e.updateQueue.baseState=n)}var rc={isMounted:function(e){return(e=e._reactInternals)?Ve(e)===e:!1},enqueueSetState:function(e,t,n){e=e._reactInternals;var l=Ht(),o=ra(l);o.payload=t,n!=null&&(o.callback=n),t=ia(e,o,l),t!==null&&(At(t,e,l),Sr(t,e,l))},enqueueReplaceState:function(e,t,n){e=e._reactInternals;var l=Ht(),o=ra(l);o.tag=1,o.payload=t,n!=null&&(o.callback=n),t=ia(e,o,l),t!==null&&(At(t,e,l),Sr(t,e,l))},enqueueForceUpdate:function(e,t){e=e._reactInternals;var n=Ht(),l=ra(n);l.tag=2,t!=null&&(l.callback=t),t=ia(e,l,n),t!==null&&(At(t,e,n),Sr(t,e,n))}};function rh(e,t,n,l,o,c,d){return e=e.stateNode,typeof e.shouldComponentUpdate=="function"?e.shouldComponentUpdate(l,c,d):t.prototype&&t.prototype.isPureReactComponent?!rr(n,l)||!rr(o,c):!0}function ih(e,t,n,l){e=t.state,typeof t.componentWillReceiveProps=="function"&&t.componentWillReceiveProps(n,l),typeof t.UNSAFE_componentWillReceiveProps=="function"&&t.UNSAFE_componentWillReceiveProps(n,l),t.state!==e&&rc.enqueueReplaceState(t,t.state,null)}function Va(e,t){var n=t;if("ref"in t){n={};for(var l in t)l!=="ref"&&(n[l]=t[l])}if(e=e.defaultProps){n===t&&(n=W({},n));for(var o in e)n[o]===void 0&&(n[o]=e[o])}return n}var $i=typeof reportError=="function"?reportError:function(e){if(typeof window=="object"&&typeof window.ErrorEvent=="function"){var t=new window.ErrorEvent("error",{bubbles:!0,cancelable:!0,message:typeof e=="object"&&e!==null&&typeof e.message=="string"?String(e.message):String(e),error:e});if(!window.dispatchEvent(t))return}else if(typeof process=="object"&&typeof process.emit=="function"){process.emit("uncaughtException",e);return}console.error(e)};function oh(e){$i(e)}function uh(e){console.error(e)}function ch(e){$i(e)}function Pi(e,t){try{var n=e.onUncaughtError;n(t.value,{componentStack:t.stack})}catch(l){setTimeout(function(){throw l})}}function sh(e,t,n){try{var l=e.onCaughtError;l(n.value,{componentStack:n.stack,errorBoundary:t.tag===1?t.stateNode:null})}catch(o){setTimeout(function(){throw o})}}function ic(e,t,n){return n=ra(n),n.tag=3,n.payload={element:null},n.callback=function(){Pi(e,t)},n}function fh(e){return e=ra(e),e.tag=3,e}function dh(e,t,n,l){var o=n.type.getDerivedStateFromError;if(typeof o=="function"){var c=l.value;e.payload=function(){return o(c)},e.callback=function(){sh(t,n,l)}}var d=n.stateNode;d!==null&&typeof d.componentDidCatch=="function"&&(e.callback=function(){sh(t,n,l),typeof o!="function"&&(da===null?da=new Set([this]):da.add(this));var b=l.stack;this.componentDidCatch(l.value,{componentStack:b!==null?b:""})})}function cb(e,t,n,l,o){if(n.flags|=32768,l!==null&&typeof l=="object"&&typeof l.then=="function"){if(t=n.alternate,t!==null&&xr(t,n,o,!0),n=Zt.current,n!==null){switch(n.tag){case 13:return pn===null?jc():n.alternate===null&&Fe===0&&(Fe=3),n.flags&=-257,n.flags|=65536,n.lanes=o,l===qu?n.flags|=16384:(t=n.updateQueue,t===null?n.updateQueue=new Set([l]):t.add(l),Xc(e,l,o)),!1;case 22:return n.flags|=65536,l===qu?n.flags|=16384:(t=n.updateQueue,t===null?(t={transitions:null,markerInstances:null,retryQueue:new Set([l])},n.updateQueue=t):(n=t.retryQueue,n===null?t.retryQueue=new Set([l]):n.add(l)),Xc(e,l,o)),!1}throw Error(u(435,n.tag))}return Xc(e,l,o),jc(),!1}if(we)return t=Zt.current,t!==null?((t.flags&65536)===0&&(t.flags|=256),t.flags|=65536,t.lanes=o,l!==Uu&&(e=Error(u(422),{cause:l}),cr(Xt(e,n)))):(l!==Uu&&(t=Error(u(423),{cause:l}),cr(Xt(t,n))),e=e.current.alternate,e.flags|=65536,o&=-o,e.lanes|=o,l=Xt(l,n),o=ic(e.stateNode,l,o),Sc(e,o),Fe!==4&&(Fe=2)),!1;var c=Error(u(520),{cause:l});if(c=Xt(c,n),wr===null?wr=[c]:wr.push(c),Fe!==4&&(Fe=2),t===null)return!0;l=Xt(l,n),n=t;do{switch(n.tag){case 3:return n.flags|=65536,e=o&-o,n.lanes|=e,e=ic(n.stateNode,l,e),Sc(n,e),!1;case 1:if(t=n.type,c=n.stateNode,(n.flags&128)===0&&(typeof t.getDerivedStateFromError=="function"||c!==null&&typeof c.componentDidCatch=="function"&&(da===null||!da.has(c))))return n.flags|=65536,o&=-o,n.lanes|=o,o=fh(o),dh(o,e,n,l),Sc(n,o),!1}n=n.return}while(n!==null);return!1}var hh=Error(u(461)),ft=!1;function vt(e,t,n,l){t.child=e===null?gd(t,null,n,l):Ua(t,e.child,n,l)}function mh(e,t,n,l,o){n=n.render;var c=t.ref;if("ref"in l){var d={};for(var b in l)b!=="ref"&&(d[b]=l[b])}else d=l;return Ya(t),l=Xu(e,t,n,d,c,o),b=Gu(),e!==null&&!ft?(Qu(e,t,o),Rn(e,t,o)):(we&&b&&ku(t),t.flags|=1,vt(e,t,l,o),t.child)}function ph(e,t,n,l,o){if(e===null){var c=n.type;return typeof c=="function"&&!wc(c)&&c.defaultProps===void 0&&n.compare===null?(t.tag=15,t.type=c,vh(e,t,c,l,o)):(e=Ii(n.type,null,l,t,t.mode,o),e.ref=t.ref,e.return=t,t.child=e)}if(c=e.child,!pc(e,o)){var d=c.memoizedProps;if(n=n.compare,n=n!==null?n:rr,n(d,l)&&e.ref===t.ref)return Rn(e,t,o)}return t.flags|=1,e=sa(c,l),e.ref=t.ref,e.return=t,t.child=e}function vh(e,t,n,l,o){if(e!==null){var c=e.memoizedProps;if(rr(c,l)&&e.ref===t.ref)if(ft=!1,t.pendingProps=l=c,pc(e,o))(e.flags&131072)!==0&&(ft=!0);else return t.lanes=e.lanes,Rn(e,t,o)}return oc(e,t,n,l,o)}function bh(e,t,n){var l=t.pendingProps,o=l.children,c=(t.stateNode._pendingVisibility&2)!==0,d=e!==null?e.memoizedState:null;if(yr(e,t),l.mode==="hidden"||c){if((t.flags&128)!==0){if(l=d!==null?d.baseLanes|n:n,e!==null){for(o=t.child=e.child,c=0;o!==null;)c=c|o.lanes|o.childLanes,o=o.sibling;t.childLanes=c&~l}else t.childLanes=0,t.child=null;return gh(e,t,l,n)}if((n&536870912)!==0)t.memoizedState={baseLanes:0,cachePool:null},e!==null&&Li(t,d!==null?d.cachePool:null),d!==null?yd(t,d):Hu(),xd(t);else return t.lanes=t.childLanes=536870912,gh(e,t,d!==null?d.baseLanes|n:n,n)}else d!==null?(Li(t,d.cachePool),yd(t,d),ea(),t.memoizedState=null):(e!==null&&Li(t,null),Hu(),ea());return vt(e,t,o,n),t.child}function gh(e,t,n,l){var o=ju();return o=o===null?null:{parent:it._currentValue,pool:o},t.memoizedState={baseLanes:n,cachePool:o},e!==null&&Li(t,null),Hu(),xd(t),e!==null&&xr(e,t,l,!0),null}function yr(e,t){var n=t.ref;if(n===null)e!==null&&e.ref!==null&&(t.flags|=2097664);else{if(typeof n!="function"&&typeof n!="object")throw Error(u(284));(e===null||e.ref!==n)&&(t.flags|=2097664)}}function oc(e,t,n,l,o){return Ya(t),n=Xu(e,t,n,l,void 0,o),l=Gu(),e!==null&&!ft?(Qu(e,t,o),Rn(e,t,o)):(we&&l&&ku(t),t.flags|=1,vt(e,t,n,o),t.child)}function yh(e,t,n,l,o,c){return Ya(t),t.updateQueue=null,n=Td(t,l,n,o),Ad(e),l=Gu(),e!==null&&!ft?(Qu(e,t,c),Rn(e,t,c)):(we&&l&&ku(t),t.flags|=1,vt(e,t,n,c),t.child)}function xh(e,t,n,l,o){if(Ya(t),t.stateNode===null){var c=vl,d=n.contextType;typeof d=="object"&&d!==null&&(c=xt(d)),c=new n(l,c),t.memoizedState=c.state!==null&&c.state!==void 0?c.state:null,c.updater=rc,t.stateNode=c,c._reactInternals=t,c=t.stateNode,c.props=l,c.state=t.memoizedState,c.refs={},yc(t),d=n.contextType,c.context=typeof d=="object"&&d!==null?xt(d):vl,c.state=t.memoizedState,d=n.getDerivedStateFromProps,typeof d=="function"&&(lc(t,n,d,l),c.state=t.memoizedState),typeof n.getDerivedStateFromProps=="function"||typeof c.getSnapshotBeforeUpdate=="function"||typeof c.UNSAFE_componentWillMount!="function"&&typeof c.componentWillMount!="function"||(d=c.state,typeof c.componentWillMount=="function"&&c.componentWillMount(),typeof c.UNSAFE_componentWillMount=="function"&&c.UNSAFE_componentWillMount(),d!==c.state&&rc.enqueueReplaceState(c,c.state,null),Or(t,l,c,o),Er(),c.state=t.memoizedState),typeof c.componentDidMount=="function"&&(t.flags|=4194308),l=!0}else if(e===null){c=t.stateNode;var b=t.memoizedProps,x=Va(n,b);c.props=x;var _=c.context,B=n.contextType;d=vl,typeof B=="object"&&B!==null&&(d=xt(B));var Q=n.getDerivedStateFromProps;B=typeof Q=="function"||typeof c.getSnapshotBeforeUpdate=="function",b=t.pendingProps!==b,B||typeof c.UNSAFE_componentWillReceiveProps!="function"&&typeof c.componentWillReceiveProps!="function"||(b||_!==d)&&ih(t,c,l,d),la=!1;var k=t.memoizedState;c.state=k,Or(t,l,c,o),Er(),_=t.memoizedState,b||k!==_||la?(typeof Q=="function"&&(lc(t,n,Q,l),_=t.memoizedState),(x=la||rh(t,n,x,l,k,_,d))?(B||typeof c.UNSAFE_componentWillMount!="function"&&typeof c.componentWillMount!="function"||(typeof c.componentWillMount=="function"&&c.componentWillMount(),typeof c.UNSAFE_componentWillMount=="function"&&c.UNSAFE_componentWillMount()),typeof c.componentDidMount=="function"&&(t.flags|=4194308)):(typeof c.componentDidMount=="function"&&(t.flags|=4194308),t.memoizedProps=l,t.memoizedState=_),c.props=l,c.state=_,c.context=d,l=x):(typeof c.componentDidMount=="function"&&(t.flags|=4194308),l=!1)}else{c=t.stateNode,xc(e,t),d=t.memoizedProps,B=Va(n,d),c.props=B,Q=t.pendingProps,k=c.context,_=n.contextType,x=vl,typeof _=="object"&&_!==null&&(x=xt(_)),b=n.getDerivedStateFromProps,(_=typeof b=="function"||typeof c.getSnapshotBeforeUpdate=="function")||typeof c.UNSAFE_componentWillReceiveProps!="function"&&typeof c.componentWillReceiveProps!="function"||(d!==Q||k!==x)&&ih(t,c,l,x),la=!1,k=t.memoizedState,c.state=k,Or(t,l,c,o),Er();var L=t.memoizedState;d!==Q||k!==L||la||e!==null&&e.dependencies!==null&&Fi(e.dependencies)?(typeof b=="function"&&(lc(t,n,b,l),L=t.memoizedState),(B=la||rh(t,n,B,l,k,L,x)||e!==null&&e.dependencies!==null&&Fi(e.dependencies))?(_||typeof c.UNSAFE_componentWillUpdate!="function"&&typeof c.componentWillUpdate!="function"||(typeof c.componentWillUpdate=="function"&&c.componentWillUpdate(l,L,x),typeof c.UNSAFE_componentWillUpdate=="function"&&c.UNSAFE_componentWillUpdate(l,L,x)),typeof c.componentDidUpdate=="function"&&(t.flags|=4),typeof c.getSnapshotBeforeUpdate=="function"&&(t.flags|=1024)):(typeof c.componentDidUpdate!="function"||d===e.memoizedProps&&k===e.memoizedState||(t.flags|=4),typeof c.getSnapshotBeforeUpdate!="function"||d===e.memoizedProps&&k===e.memoizedState||(t.flags|=1024),t.memoizedProps=l,t.memoizedState=L),c.props=l,c.state=L,c.context=x,l=B):(typeof c.componentDidUpdate!="function"||d===e.memoizedProps&&k===e.memoizedState||(t.flags|=4),typeof c.getSnapshotBeforeUpdate!="function"||d===e.memoizedProps&&k===e.memoizedState||(t.flags|=1024),l=!1)}return c=l,yr(e,t),l=(t.flags&128)!==0,c||l?(c=t.stateNode,n=l&&typeof n.getDerivedStateFromError!="function"?null:c.render(),t.flags|=1,e!==null&&l?(t.child=Ua(t,e.child,null,o),t.child=Ua(t,null,n,o)):vt(e,t,n,o),t.memoizedState=c.state,e=t.child):e=Rn(e,t,o),e}function Sh(e,t,n,l){return ur(),t.flags|=256,vt(e,t,n,l),t.child}var uc={dehydrated:null,treeContext:null,retryLane:0};function cc(e){return{baseLanes:e,cachePool:Od()}}function sc(e,t,n){return e=e!==null?e.childLanes&~n:0,t&&(e|=Kt),e}function Eh(e,t,n){var l=t.pendingProps,o=!1,c=(t.flags&128)!==0,d;if((d=c)||(d=e!==null&&e.memoizedState===null?!1:(rt.current&2)!==0),d&&(o=!0,t.flags&=-129),d=(t.flags&32)!==0,t.flags&=-33,e===null){if(we){if(o?In(t):ea(),we){var b=pt,x;if(x=b){e:{for(x=b,b=mn;x.nodeType!==8;){if(!b){b=null;break e}if(x=rn(x.nextSibling),x===null){b=null;break e}}b=x}b!==null?(t.memoizedState={dehydrated:b,treeContext:Ca!==null?{id:_n,overflow:zn}:null,retryLane:536870912},x=Ft(18,null,null,0),x.stateNode=b,x.return=t,t.child=x,Ot=t,pt=null,x=!0):x=!1}x||Na(t)}if(b=t.memoizedState,b!==null&&(b=b.dehydrated,b!==null))return b.data==="$!"?t.lanes=16:t.lanes=536870912,null;Dn(t)}return b=l.children,l=l.fallback,o?(ea(),o=t.mode,b=dc({mode:"hidden",children:b},o),l=Ga(l,o,n,null),b.return=t,l.return=t,b.sibling=l,t.child=b,o=t.child,o.memoizedState=cc(n),o.childLanes=sc(e,d,n),t.memoizedState=uc,l):(In(t),fc(t,b))}if(x=e.memoizedState,x!==null&&(b=x.dehydrated,b!==null)){if(c)t.flags&256?(In(t),t.flags&=-257,t=hc(e,t,n)):t.memoizedState!==null?(ea(),t.child=e.child,t.flags|=128,t=null):(ea(),o=l.fallback,b=t.mode,l=dc({mode:"visible",children:l.children},b),o=Ga(o,b,n,null),o.flags|=2,l.return=t,o.return=t,l.sibling=o,t.child=l,Ua(t,e.child,null,n),l=t.child,l.memoizedState=cc(n),l.childLanes=sc(e,d,n),t.memoizedState=uc,t=o);else if(In(t),b.data==="$!"){if(d=b.nextSibling&&b.nextSibling.dataset,d)var _=d.dgst;d=_,l=Error(u(419)),l.stack="",l.digest=d,cr({value:l,source:null,stack:null}),t=hc(e,t,n)}else if(ft||xr(e,t,n,!1),d=(n&e.childLanes)!==0,ft||d){if(d=He,d!==null){if(l=n&-n,(l&42)!==0)l=1;else switch(l){case 2:l=1;break;case 8:l=4;break;case 32:l=16;break;case 128:case 256:case 512:case 1024:case 2048:case 4096:case 8192:case 16384:case 32768:case 65536:case 131072:case 262144:case 524288:case 1048576:case 2097152:case 4194304:case 8388608:case 16777216:case 33554432:l=64;break;case 268435456:l=134217728;break;default:l=0}if(l=(l&(d.suspendedLanes|n))!==0?0:l,l!==0&&l!==x.retryLane)throw x.retryLane=l,Wn(e,l),At(d,e,l),hh}b.data==="$?"||jc(),t=hc(e,t,n)}else b.data==="$?"?(t.flags|=128,t.child=e.child,t=Ab.bind(null,e),b._reactRetry=t,t=null):(e=x.treeContext,pt=rn(b.nextSibling),Ot=t,we=!0,an=null,mn=!1,e!==null&&(Gt[Qt++]=_n,Gt[Qt++]=zn,Gt[Qt++]=Ca,_n=e.id,zn=e.overflow,Ca=t),t=fc(t,l.children),t.flags|=4096);return t}return o?(ea(),o=l.fallback,b=t.mode,x=e.child,_=x.sibling,l=sa(x,{mode:"hidden",children:l.children}),l.subtreeFlags=x.subtreeFlags&31457280,_!==null?o=sa(_,o):(o=Ga(o,b,n,null),o.flags|=2),o.return=t,l.return=t,l.sibling=o,t.child=l,l=o,o=t.child,b=e.child.memoizedState,b===null?b=cc(n):(x=b.cachePool,x!==null?(_=it._currentValue,x=x.parent!==_?{parent:_,pool:_}:x):x=Od(),b={baseLanes:b.baseLanes|n,cachePool:x}),o.memoizedState=b,o.childLanes=sc(e,d,n),t.memoizedState=uc,l):(In(t),n=e.child,e=n.sibling,n=sa(n,{mode:"visible",children:l.children}),n.return=t,n.sibling=null,e!==null&&(d=t.deletions,d===null?(t.deletions=[e],t.flags|=16):d.push(e)),t.child=n,t.memoizedState=null,n)}function fc(e,t){return t=dc({mode:"visible",children:t},e.mode),t.return=e,e.child=t}function dc(e,t){return Ph(e,t,0,null)}function hc(e,t,n){return Ua(t,e.child,null,n),e=fc(t,t.pendingProps.children),e.flags|=2,t.memoizedState=null,e}function Oh(e,t,n){e.lanes|=t;var l=e.alternate;l!==null&&(l.lanes|=t),bc(e.return,t,n)}function mc(e,t,n,l,o){var c=e.memoizedState;c===null?e.memoizedState={isBackwards:t,rendering:null,renderingStartTime:0,last:l,tail:n,tailMode:o}:(c.isBackwards=t,c.rendering=null,c.renderingStartTime=0,c.last=l,c.tail=n,c.tailMode=o)}function Ah(e,t,n){var l=t.pendingProps,o=l.revealOrder,c=l.tail;if(vt(e,t,l.children,n),l=rt.current,(l&2)!==0)l=l&1|2,t.flags|=128;else{if(e!==null&&(e.flags&128)!==0)e:for(e=t.child;e!==null;){if(e.tag===13)e.memoizedState!==null&&Oh(e,n,t);else if(e.tag===19)Oh(e,n,t);else if(e.child!==null){e.child.return=e,e=e.child;continue}if(e===t)break e;for(;e.sibling===null;){if(e.return===null||e.return===t)break e;e=e.return}e.sibling.return=e.return,e=e.sibling}l&=1}switch(Ne(rt,l),o){case"forwards":for(n=t.child,o=null;n!==null;)e=n.alternate,e!==null&&Hi(e)===null&&(o=n),n=n.sibling;n=o,n===null?(o=t.child,t.child=null):(o=n.sibling,n.sibling=null),mc(t,!1,o,n,c);break;case"backwards":for(n=null,o=t.child,t.child=null;o!==null;){if(e=o.alternate,e!==null&&Hi(e)===null){t.child=o;break}e=o.sibling,o.sibling=n,n=o,o=e}mc(t,!0,n,null,c);break;case"together":mc(t,!1,null,null,void 0);break;default:t.memoizedState=null}return t.child}function Rn(e,t,n){if(e!==null&&(t.dependencies=e.dependencies),fa|=t.lanes,(n&t.childLanes)===0)if(e!==null){if(xr(e,t,n,!1),(n&t.childLanes)===0)return null}else return null;if(e!==null&&t.child!==e.child)throw Error(u(153));if(t.child!==null){for(e=t.child,n=sa(e,e.pendingProps),t.child=n,n.return=t;e.sibling!==null;)e=e.sibling,n=n.sibling=sa(e,e.pendingProps),n.return=t;n.sibling=null}return t.child}function pc(e,t){return(e.lanes&t)!==0?!0:(e=e.dependencies,!!(e!==null&&Fi(e)))}function sb(e,t,n){switch(t.tag){case 3:el(t,t.stateNode.containerInfo),aa(t,it,e.memoizedState.cache),ur();break;case 27:case 5:tl(t);break;case 4:el(t,t.stateNode.containerInfo);break;case 10:aa(t,t.type,t.memoizedProps.value);break;case 13:var l=t.memoizedState;if(l!==null)return l.dehydrated!==null?(In(t),t.flags|=128,null):(n&t.child.childLanes)!==0?Eh(e,t,n):(In(t),e=Rn(e,t,n),e!==null?e.sibling:null);In(t);break;case 19:var o=(e.flags&128)!==0;if(l=(n&t.childLanes)!==0,l||(xr(e,t,n,!1),l=(n&t.childLanes)!==0),o){if(l)return Ah(e,t,n);t.flags|=128}if(o=t.memoizedState,o!==null&&(o.rendering=null,o.tail=null,o.lastEffect=null),Ne(rt,rt.current),l)break;return null;case 22:case 23:return t.lanes=0,bh(e,t,n);case 24:aa(t,it,e.memoizedState.cache)}return Rn(e,t,n)}function Th(e,t,n){if(e!==null)if(e.memoizedProps!==t.pendingProps)ft=!0;else{if(!pc(e,n)&&(t.flags&128)===0)return ft=!1,sb(e,t,n);ft=(e.flags&131072)!==0}else ft=!1,we&&(t.flags&1048576)!==0&&cd(t,Ci,t.index);switch(t.lanes=0,t.tag){case 16:e:{e=t.pendingProps;var l=t.elementType,o=l._init;if(l=o(l._payload),t.type=l,typeof l=="function")wc(l)?(e=Va(l,e),t.tag=1,t=xh(null,t,l,e,n)):(t.tag=0,t=oc(null,t,l,e,n));else{if(l!=null){if(o=l.$$typeof,o===A){t.tag=11,t=mh(null,t,l,e,n);break e}else if(o===q){t.tag=14,t=ph(null,t,l,e,n);break e}}throw t=ve(l)||l,Error(u(306,t,""))}}return t;case 0:return oc(e,t,t.type,t.pendingProps,n);case 1:return l=t.type,o=Va(l,t.pendingProps),xh(e,t,l,o,n);case 3:e:{if(el(t,t.stateNode.containerInfo),e===null)throw Error(u(387));var c=t.pendingProps;o=t.memoizedState,l=o.element,xc(e,t),Or(t,c,null,n);var d=t.memoizedState;if(c=d.cache,aa(t,it,c),c!==o.cache&&gc(t,[it],n,!0),Er(),c=d.element,o.isDehydrated)if(o={element:c,isDehydrated:!1,cache:d.cache},t.updateQueue.baseState=o,t.memoizedState=o,t.flags&256){t=Sh(e,t,c,n);break e}else if(c!==l){l=Xt(Error(u(424)),t),cr(l),t=Sh(e,t,c,n);break e}else for(pt=rn(t.stateNode.containerInfo.firstChild),Ot=t,we=!0,an=null,mn=!0,n=gd(t,null,c,n),t.child=n;n;)n.flags=n.flags&-3|4096,n=n.sibling;else{if(ur(),c===l){t=Rn(e,t,n);break e}vt(e,t,c,n)}t=t.child}return t;case 26:return yr(e,t),e===null?(n=D0(t.type,null,t.pendingProps,null))?t.memoizedState=n:we||(n=t.type,e=t.pendingProps,l=fo(hn.current).createElement(n),l[yt]=t,l[wt]=e,bt(l,n,e),st(l),t.stateNode=l):t.memoizedState=D0(t.type,e.memoizedProps,t.pendingProps,e.memoizedState),null;case 27:return tl(t),e===null&&we&&(l=t.stateNode=T0(t.type,t.pendingProps,hn.current),Ot=t,mn=!0,pt=rn(l.firstChild)),l=t.pendingProps.children,e!==null||we?vt(e,t,l,n):t.child=Ua(t,null,l,n),yr(e,t),t.child;case 5:return e===null&&we&&((o=l=pt)&&(l=Vb(l,t.type,t.pendingProps,mn),l!==null?(t.stateNode=l,Ot=t,pt=rn(l.firstChild),mn=!1,o=!0):o=!1),o||Na(t)),tl(t),o=t.type,c=t.pendingProps,d=e!==null?e.memoizedProps:null,l=c.children,ts(o,c)?l=null:d!==null&&ts(o,d)&&(t.flags|=32),t.memoizedState!==null&&(o=Xu(e,t,ab,null,null,n),Lr._currentValue=o),yr(e,t),vt(e,t,l,n),t.child;case 6:return e===null&&we&&((e=n=pt)&&(n=jb(n,t.pendingProps,mn),n!==null?(t.stateNode=n,Ot=t,pt=null,e=!0):e=!1),e||Na(t)),null;case 13:return Eh(e,t,n);case 4:return el(t,t.stateNode.containerInfo),l=t.pendingProps,e===null?t.child=Ua(t,null,l,n):vt(e,t,l,n),t.child;case 11:return mh(e,t,t.type,t.pendingProps,n);case 7:return vt(e,t,t.pendingProps,n),t.child;case 8:return vt(e,t,t.pendingProps.children,n),t.child;case 12:return vt(e,t,t.pendingProps.children,n),t.child;case 10:return l=t.pendingProps,aa(t,t.type,l.value),vt(e,t,l.children,n),t.child;case 9:return o=t.type._context,l=t.pendingProps.children,Ya(t),o=xt(o),l=l(o),t.flags|=1,vt(e,t,l,n),t.child;case 14:return ph(e,t,t.type,t.pendingProps,n);case 15:return vh(e,t,t.type,t.pendingProps,n);case 19:return Ah(e,t,n);case 22:return bh(e,t,n);case 24:return Ya(t),l=xt(it),e===null?(o=ju(),o===null&&(o=He,c=Bu(),o.pooledCache=c,c.refCount++,c!==null&&(o.pooledCacheLanes|=n),o=c),t.memoizedState={parent:l,cache:o},yc(t),aa(t,it,o)):((e.lanes&n)!==0&&(xc(e,t),Or(t,null,null,n),Er()),o=e.memoizedState,c=t.memoizedState,o.parent!==l?(o={parent:l,cache:l},t.memoizedState=o,t.lanes===0&&(t.memoizedState=t.updateQueue.baseState=o),aa(t,it,l)):(l=c.cache,aa(t,it,l),l!==o.cache&&gc(t,[it],n,!0))),vt(e,t,t.pendingProps.children,n),t.child;case 29:throw t.pendingProps}throw Error(u(156,t.tag))}var vc=Se(null),ja=null,Mn=null;function aa(e,t,n){Ne(vc,t._currentValue),t._currentValue=n}function Cn(e){e._currentValue=vc.current,Me(vc)}function bc(e,t,n){for(;e!==null;){var l=e.alternate;if((e.childLanes&t)!==t?(e.childLanes|=t,l!==null&&(l.childLanes|=t)):l!==null&&(l.childLanes&t)!==t&&(l.childLanes|=t),e===n)break;e=e.return}}function gc(e,t,n,l){var o=e.child;for(o!==null&&(o.return=e);o!==null;){var c=o.dependencies;if(c!==null){var d=o.child;c=c.firstContext;e:for(;c!==null;){var b=c;c=o;for(var x=0;x<t.length;x++)if(b.context===t[x]){c.lanes|=n,b=c.alternate,b!==null&&(b.lanes|=n),bc(c.return,n,e),l||(d=null);break e}c=b.next}}else if(o.tag===18){if(d=o.return,d===null)throw Error(u(341));d.lanes|=n,c=d.alternate,c!==null&&(c.lanes|=n),bc(d,n,e),d=null}else d=o.child;if(d!==null)d.return=o;else for(d=o;d!==null;){if(d===e){d=null;break}if(o=d.sibling,o!==null){o.return=d.return,d=o;break}d=d.return}o=d}}function xr(e,t,n,l){e=null;for(var o=t,c=!1;o!==null;){if(!c){if((o.flags&524288)!==0)c=!0;else if((o.flags&262144)!==0)break}if(o.tag===10){var d=o.alternate;if(d===null)throw Error(u(387));if(d=d.memoizedProps,d!==null){var b=o.type;kt(o.pendingProps.value,d.value)||(e!==null?e.push(b):e=[b])}}else if(o===Ta.current){if(d=o.alternate,d===null)throw Error(u(387));d.memoizedState.memoizedState!==o.memoizedState.memoizedState&&(e!==null?e.push(Lr):e=[Lr])}o=o.return}e!==null&&gc(t,e,n,l),t.flags|=262144}function Fi(e){for(e=e.firstContext;e!==null;){if(!kt(e.context._currentValue,e.memoizedValue))return!0;e=e.next}return!1}function Ya(e){ja=e,Mn=null,e=e.dependencies,e!==null&&(e.firstContext=null)}function xt(e){return _h(ja,e)}function Ki(e,t){return ja===null&&Ya(e),_h(e,t)}function _h(e,t){var n=t._currentValue;if(t={context:t,memoizedValue:n,next:null},Mn===null){if(e===null)throw Error(u(308));Mn=t,e.dependencies={lanes:0,firstContext:t},e.flags|=524288}else Mn=Mn.next=t;return n}var la=!1;function yc(e){e.updateQueue={baseState:e.memoizedState,firstBaseUpdate:null,lastBaseUpdate:null,shared:{pending:null,lanes:0,hiddenCallbacks:null},callbacks:null}}function xc(e,t){e=e.updateQueue,t.updateQueue===e&&(t.updateQueue={baseState:e.baseState,firstBaseUpdate:e.firstBaseUpdate,lastBaseUpdate:e.lastBaseUpdate,shared:e.shared,callbacks:null})}function ra(e){return{lane:e,tag:0,payload:null,callback:null,next:null}}function ia(e,t,n){var l=e.updateQueue;if(l===null)return null;if(l=l.shared,(Qe&2)!==0){var o=l.pending;return o===null?t.next=t:(t.next=o.next,o.next=t),l.pending=t,t=Ri(e),od(e,null,n),t}return wi(e,l,t,n),Ri(e)}function Sr(e,t,n){if(t=t.updateQueue,t!==null&&(t=t.shared,(n&4194176)!==0)){var l=t.lanes;l&=e.pendingLanes,n|=l,t.lanes=n,pf(e,n)}}function Sc(e,t){var n=e.updateQueue,l=e.alternate;if(l!==null&&(l=l.updateQueue,n===l)){var o=null,c=null;if(n=n.firstBaseUpdate,n!==null){do{var d={lane:n.lane,tag:n.tag,payload:n.payload,callback:null,next:null};c===null?o=c=d:c=c.next=d,n=n.next}while(n!==null);c===null?o=c=t:c=c.next=t}else o=c=t;n={baseState:l.baseState,firstBaseUpdate:o,lastBaseUpdate:c,shared:l.shared,callbacks:l.callbacks},e.updateQueue=n;return}e=n.lastBaseUpdate,e===null?n.firstBaseUpdate=t:e.next=t,n.lastBaseUpdate=t}var Ec=!1;function Er(){if(Ec){var e=El;if(e!==null)throw e}}function Or(e,t,n,l){Ec=!1;var o=e.updateQueue;la=!1;var c=o.firstBaseUpdate,d=o.lastBaseUpdate,b=o.shared.pending;if(b!==null){o.shared.pending=null;var x=b,_=x.next;x.next=null,d===null?c=_:d.next=_,d=x;var B=e.alternate;B!==null&&(B=B.updateQueue,b=B.lastBaseUpdate,b!==d&&(b===null?B.firstBaseUpdate=_:b.next=_,B.lastBaseUpdate=x))}if(c!==null){var Q=o.baseState;d=0,B=_=x=null,b=c;do{var k=b.lane&-536870913,L=k!==b.lane;if(L?(De&k)===k:(l&k)===k){k!==0&&k===Sl&&(Ec=!0),B!==null&&(B=B.next={lane:0,tag:b.tag,payload:b.payload,callback:null,next:null});e:{var le=e,fe=b;k=t;var Ke=n;switch(fe.tag){case 1:if(le=fe.payload,typeof le=="function"){Q=le.call(Ke,Q,k);break e}Q=le;break e;case 3:le.flags=le.flags&-65537|128;case 0:if(le=fe.payload,k=typeof le=="function"?le.call(Ke,Q,k):le,k==null)break e;Q=W({},Q,k);break e;case 2:la=!0}}k=b.callback,k!==null&&(e.flags|=64,L&&(e.flags|=8192),L=o.callbacks,L===null?o.callbacks=[k]:L.push(k))}else L={lane:k,tag:b.tag,payload:b.payload,callback:b.callback,next:null},B===null?(_=B=L,x=Q):B=B.next=L,d|=k;if(b=b.next,b===null){if(b=o.shared.pending,b===null)break;L=b,b=L.next,L.next=null,o.lastBaseUpdate=L,o.shared.pending=null}}while(!0);B===null&&(x=Q),o.baseState=x,o.firstBaseUpdate=_,o.lastBaseUpdate=B,c===null&&(o.shared.lanes=0),fa|=d,e.lanes=d,e.memoizedState=Q}}function zh(e,t){if(typeof e!="function")throw Error(u(191,e));e.call(t)}function Dh(e,t){var n=e.callbacks;if(n!==null)for(e.callbacks=null,e=0;e<n.length;e++)zh(n[e],t)}function Ar(e,t){try{var n=t.updateQueue,l=n!==null?n.lastEffect:null;if(l!==null){var o=l.next;n=o;do{if((n.tag&e)===e){l=void 0;var c=n.create,d=n.inst;l=c(),d.destroy=l}n=n.next}while(n!==o)}}catch(b){Ue(t,t.return,b)}}function oa(e,t,n){try{var l=t.updateQueue,o=l!==null?l.lastEffect:null;if(o!==null){var c=o.next;l=c;do{if((l.tag&e)===e){var d=l.inst,b=d.destroy;if(b!==void 0){d.destroy=void 0,o=t;var x=n;try{b()}catch(_){Ue(o,x,_)}}}l=l.next}while(l!==c)}}catch(_){Ue(t,t.return,_)}}function wh(e){var t=e.updateQueue;if(t!==null){var n=e.stateNode;try{Dh(t,n)}catch(l){Ue(e,e.return,l)}}}function Rh(e,t,n){n.props=Va(e.type,e.memoizedProps),n.state=e.memoizedState;try{n.componentWillUnmount()}catch(l){Ue(e,t,l)}}function Xa(e,t){try{var n=e.ref;if(n!==null){var l=e.stateNode;switch(e.tag){case 26:case 27:case 5:var o=l;break;default:o=l}typeof n=="function"?e.refCleanup=n(o):n.current=o}}catch(c){Ue(e,t,c)}}function Nt(e,t){var n=e.ref,l=e.refCleanup;if(n!==null)if(typeof l=="function")try{l()}catch(o){Ue(e,t,o)}finally{e.refCleanup=null,e=e.alternate,e!=null&&(e.refCleanup=null)}else if(typeof n=="function")try{n(null)}catch(o){Ue(e,t,o)}else n.current=null}function Mh(e){var t=e.type,n=e.memoizedProps,l=e.stateNode;try{e:switch(t){case"button":case"input":case"select":case"textarea":n.autoFocus&&l.focus();break e;case"img":n.src?l.src=n.src:n.srcSet&&(l.srcset=n.srcSet)}}catch(o){Ue(e,e.return,o)}}function Ch(e,t,n){try{var l=e.stateNode;Ub(l,e.type,n,t),l[wt]=t}catch(o){Ue(e,e.return,o)}}function kh(e){return e.tag===5||e.tag===3||e.tag===26||e.tag===27||e.tag===4}function Oc(e){e:for(;;){for(;e.sibling===null;){if(e.return===null||kh(e.return))return null;e=e.return}for(e.sibling.return=e.return,e=e.sibling;e.tag!==5&&e.tag!==6&&e.tag!==27&&e.tag!==18;){if(e.flags&2||e.child===null||e.tag===4)continue e;e.child.return=e,e=e.child}if(!(e.flags&2))return e.stateNode}}function Ac(e,t,n){var l=e.tag;if(l===5||l===6)e=e.stateNode,t?n.nodeType===8?n.parentNode.insertBefore(e,t):n.insertBefore(e,t):(n.nodeType===8?(t=n.parentNode,t.insertBefore(e,n)):(t=n,t.appendChild(e)),n=n._reactRootContainer,n!=null||t.onclick!==null||(t.onclick=so));else if(l!==4&&l!==27&&(e=e.child,e!==null))for(Ac(e,t,n),e=e.sibling;e!==null;)Ac(e,t,n),e=e.sibling}function Ji(e,t,n){var l=e.tag;if(l===5||l===6)e=e.stateNode,t?n.insertBefore(e,t):n.appendChild(e);else if(l!==4&&l!==27&&(e=e.child,e!==null))for(Ji(e,t,n),e=e.sibling;e!==null;)Ji(e,t,n),e=e.sibling}var kn=!1,Pe=!1,Tc=!1,Nh=typeof WeakSet=="function"?WeakSet:Set,dt=null,Uh=!1;function fb(e,t){if(e=e.containerInfo,Ic=go,e=Wf(e),zu(e)){if("selectionStart"in e)var n={start:e.selectionStart,end:e.selectionEnd};else e:{n=(n=e.ownerDocument)&&n.defaultView||window;var l=n.getSelection&&n.getSelection();if(l&&l.rangeCount!==0){n=l.anchorNode;var o=l.anchorOffset,c=l.focusNode;l=l.focusOffset;try{n.nodeType,c.nodeType}catch{n=null;break e}var d=0,b=-1,x=-1,_=0,B=0,Q=e,k=null;t:for(;;){for(var L;Q!==n||o!==0&&Q.nodeType!==3||(b=d+o),Q!==c||l!==0&&Q.nodeType!==3||(x=d+l),Q.nodeType===3&&(d+=Q.nodeValue.length),(L=Q.firstChild)!==null;)k=Q,Q=L;for(;;){if(Q===e)break t;if(k===n&&++_===o&&(b=d),k===c&&++B===l&&(x=d),(L=Q.nextSibling)!==null)break;Q=k,k=Q.parentNode}Q=L}n=b===-1||x===-1?null:{start:b,end:x}}else n=null}n=n||{start:0,end:0}}else n=null;for(es={focusedElem:e,selectionRange:n},go=!1,dt=t;dt!==null;)if(t=dt,e=t.child,(t.subtreeFlags&1028)!==0&&e!==null)e.return=t,dt=e;else for(;dt!==null;){switch(t=dt,c=t.alternate,e=t.flags,t.tag){case 0:break;case 11:case 15:break;case 1:if((e&1024)!==0&&c!==null){e=void 0,n=t,o=c.memoizedProps,c=c.memoizedState,l=n.stateNode;try{var le=Va(n.type,o,n.elementType===n.type);e=l.getSnapshotBeforeUpdate(le,c),l.__reactInternalSnapshotBeforeUpdate=e}catch(fe){Ue(n,n.return,fe)}}break;case 3:if((e&1024)!==0){if(e=t.stateNode.containerInfo,n=e.nodeType,n===9)ls(e);else if(n===1)switch(e.nodeName){case"HEAD":case"HTML":case"BODY":ls(e);break;default:e.textContent=""}}break;case 5:case 26:case 27:case 6:case 4:case 17:break;default:if((e&1024)!==0)throw Error(u(163))}if(e=t.sibling,e!==null){e.return=t.return,dt=e;break}dt=t.return}return le=Uh,Uh=!1,le}function qh(e,t,n){var l=n.flags;switch(n.tag){case 0:case 11:case 15:Un(e,n),l&4&&Ar(5,n);break;case 1:if(Un(e,n),l&4)if(e=n.stateNode,t===null)try{e.componentDidMount()}catch(b){Ue(n,n.return,b)}else{var o=Va(n.type,t.memoizedProps);t=t.memoizedState;try{e.componentDidUpdate(o,t,e.__reactInternalSnapshotBeforeUpdate)}catch(b){Ue(n,n.return,b)}}l&64&&wh(n),l&512&&Xa(n,n.return);break;case 3:if(Un(e,n),l&64&&(l=n.updateQueue,l!==null)){if(e=null,n.child!==null)switch(n.child.tag){case 27:case 5:e=n.child.stateNode;break;case 1:e=n.child.stateNode}try{Dh(l,e)}catch(b){Ue(n,n.return,b)}}break;case 26:Un(e,n),l&512&&Xa(n,n.return);break;case 27:case 5:Un(e,n),t===null&&l&4&&Mh(n),l&512&&Xa(n,n.return);break;case 12:Un(e,n);break;case 13:Un(e,n),l&4&&Bh(e,n);break;case 22:if(o=n.memoizedState!==null||kn,!o){t=t!==null&&t.memoizedState!==null||Pe;var c=kn,d=Pe;kn=o,(Pe=t)&&!d?ua(e,n,(n.subtreeFlags&8772)!==0):Un(e,n),kn=c,Pe=d}l&512&&(n.memoizedProps.mode==="manual"?Xa(n,n.return):Nt(n,n.return));break;default:Un(e,n)}}function Hh(e){var t=e.alternate;t!==null&&(e.alternate=null,Hh(t)),e.child=null,e.deletions=null,e.sibling=null,e.tag===5&&(t=e.stateNode,t!==null&&fu(t)),e.stateNode=null,e.return=null,e.dependencies=null,e.memoizedProps=null,e.memoizedState=null,e.pendingProps=null,e.stateNode=null,e.updateQueue=null}var lt=null,Ut=!1;function Nn(e,t,n){for(n=n.child;n!==null;)Lh(e,t,n),n=n.sibling}function Lh(e,t,n){if(mt&&typeof mt.onCommitFiberUnmount=="function")try{mt.onCommitFiberUnmount(On,n)}catch{}switch(n.tag){case 26:Pe||Nt(n,t),Nn(e,t,n),n.memoizedState?n.memoizedState.count--:n.stateNode&&(n=n.stateNode,n.parentNode.removeChild(n));break;case 27:Pe||Nt(n,t);var l=lt,o=Ut;for(lt=n.stateNode,Nn(e,t,n),n=n.stateNode,t=n.attributes;t.length;)n.removeAttributeNode(t[0]);fu(n),lt=l,Ut=o;break;case 5:Pe||Nt(n,t);case 6:o=lt;var c=Ut;if(lt=null,Nn(e,t,n),lt=o,Ut=c,lt!==null)if(Ut)try{e=lt,l=n.stateNode,e.nodeType===8?e.parentNode.removeChild(l):e.removeChild(l)}catch(d){Ue(n,t,d)}else try{lt.removeChild(n.stateNode)}catch(d){Ue(n,t,d)}break;case 18:lt!==null&&(Ut?(t=lt,n=n.stateNode,t.nodeType===8?as(t.parentNode,n):t.nodeType===1&&as(t,n),Yr(t)):as(lt,n.stateNode));break;case 4:l=lt,o=Ut,lt=n.stateNode.containerInfo,Ut=!0,Nn(e,t,n),lt=l,Ut=o;break;case 0:case 11:case 14:case 15:Pe||oa(2,n,t),Pe||oa(4,n,t),Nn(e,t,n);break;case 1:Pe||(Nt(n,t),l=n.stateNode,typeof l.componentWillUnmount=="function"&&Rh(n,t,l)),Nn(e,t,n);break;case 21:Nn(e,t,n);break;case 22:Pe||Nt(n,t),Pe=(l=Pe)||n.memoizedState!==null,Nn(e,t,n),Pe=l;break;default:Nn(e,t,n)}}function Bh(e,t){if(t.memoizedState===null&&(e=t.alternate,e!==null&&(e=e.memoizedState,e!==null&&(e=e.dehydrated,e!==null))))try{Yr(e)}catch(n){Ue(t,t.return,n)}}function db(e){switch(e.tag){case 13:case 19:var t=e.stateNode;return t===null&&(t=e.stateNode=new Nh),t;case 22:return e=e.stateNode,t=e._retryCache,t===null&&(t=e._retryCache=new Nh),t;default:throw Error(u(435,e.tag))}}function _c(e,t){var n=db(e);t.forEach(function(l){var o=Tb.bind(null,e,l);n.has(l)||(n.add(l),l.then(o,o))})}function $t(e,t){var n=t.deletions;if(n!==null)for(var l=0;l<n.length;l++){var o=n[l],c=e,d=t,b=d;e:for(;b!==null;){switch(b.tag){case 27:case 5:lt=b.stateNode,Ut=!1;break e;case 3:lt=b.stateNode.containerInfo,Ut=!0;break e;case 4:lt=b.stateNode.containerInfo,Ut=!0;break e}b=b.return}if(lt===null)throw Error(u(160));Lh(c,d,o),lt=null,Ut=!1,c=o.alternate,c!==null&&(c.return=null),o.return=null}if(t.subtreeFlags&13878)for(t=t.child;t!==null;)Vh(t,e),t=t.sibling}var ln=null;function Vh(e,t){var n=e.alternate,l=e.flags;switch(e.tag){case 0:case 11:case 14:case 15:$t(t,e),Pt(e),l&4&&(oa(3,e,e.return),Ar(3,e),oa(5,e,e.return));break;case 1:$t(t,e),Pt(e),l&512&&(Pe||n===null||Nt(n,n.return)),l&64&&kn&&(e=e.updateQueue,e!==null&&(l=e.callbacks,l!==null&&(n=e.shared.hiddenCallbacks,e.shared.hiddenCallbacks=n===null?l:n.concat(l))));break;case 26:var o=ln;if($t(t,e),Pt(e),l&512&&(Pe||n===null||Nt(n,n.return)),l&4){var c=n!==null?n.memoizedState:null;if(l=e.memoizedState,n===null)if(l===null)if(e.stateNode===null){e:{l=e.type,n=e.memoizedProps,o=o.ownerDocument||o;t:switch(l){case"title":c=o.getElementsByTagName("title")[0],(!c||c[Kl]||c[yt]||c.namespaceURI==="http://www.w3.org/2000/svg"||c.hasAttribute("itemprop"))&&(c=o.createElement(l),o.head.insertBefore(c,o.querySelector("head > title"))),bt(c,l,n),c[yt]=e,st(c),l=c;break e;case"link":var d=M0("link","href",o).get(l+(n.href||""));if(d){for(var b=0;b<d.length;b++)if(c=d[b],c.getAttribute("href")===(n.href==null?null:n.href)&&c.getAttribute("rel")===(n.rel==null?null:n.rel)&&c.getAttribute("title")===(n.title==null?null:n.title)&&c.getAttribute("crossorigin")===(n.crossOrigin==null?null:n.crossOrigin)){d.splice(b,1);break t}}c=o.createElement(l),bt(c,l,n),o.head.appendChild(c);break;case"meta":if(d=M0("meta","content",o).get(l+(n.content||""))){for(b=0;b<d.length;b++)if(c=d[b],c.getAttribute("content")===(n.content==null?null:""+n.content)&&c.getAttribute("name")===(n.name==null?null:n.name)&&c.getAttribute("property")===(n.property==null?null:n.property)&&c.getAttribute("http-equiv")===(n.httpEquiv==null?null:n.httpEquiv)&&c.getAttribute("charset")===(n.charSet==null?null:n.charSet)){d.splice(b,1);break t}}c=o.createElement(l),bt(c,l,n),o.head.appendChild(c);break;default:throw Error(u(468,l))}c[yt]=e,st(c),l=c}e.stateNode=l}else C0(o,e.type,e.stateNode);else e.stateNode=R0(o,l,e.memoizedProps);else c!==l?(c===null?n.stateNode!==null&&(n=n.stateNode,n.parentNode.removeChild(n)):c.count--,l===null?C0(o,e.type,e.stateNode):R0(o,l,e.memoizedProps)):l===null&&e.stateNode!==null&&Ch(e,e.memoizedProps,n.memoizedProps)}break;case 27:if(l&4&&e.alternate===null){o=e.stateNode,c=e.memoizedProps;try{for(var x=o.firstChild;x;){var _=x.nextSibling,B=x.nodeName;x[Kl]||B==="HEAD"||B==="BODY"||B==="SCRIPT"||B==="STYLE"||B==="LINK"&&x.rel.toLowerCase()==="stylesheet"||o.removeChild(x),x=_}for(var Q=e.type,k=o.attributes;k.length;)o.removeAttributeNode(k[0]);bt(o,Q,c),o[yt]=e,o[wt]=c}catch(le){Ue(e,e.return,le)}}case 5:if($t(t,e),Pt(e),l&512&&(Pe||n===null||Nt(n,n.return)),e.flags&32){o=e.stateNode;try{cl(o,"")}catch(le){Ue(e,e.return,le)}}l&4&&e.stateNode!=null&&(o=e.memoizedProps,Ch(e,o,n!==null?n.memoizedProps:o)),l&1024&&(Tc=!0);break;case 6:if($t(t,e),Pt(e),l&4){if(e.stateNode===null)throw Error(u(162));l=e.memoizedProps,n=e.stateNode;try{n.nodeValue=l}catch(le){Ue(e,e.return,le)}}break;case 3:if(po=null,o=ln,ln=ho(t.containerInfo),$t(t,e),ln=o,Pt(e),l&4&&n!==null&&n.memoizedState.isDehydrated)try{Yr(t.containerInfo)}catch(le){Ue(e,e.return,le)}Tc&&(Tc=!1,jh(e));break;case 4:l=ln,ln=ho(e.stateNode.containerInfo),$t(t,e),Pt(e),ln=l;break;case 12:$t(t,e),Pt(e);break;case 13:$t(t,e),Pt(e),e.child.flags&8192&&e.memoizedState!==null!=(n!==null&&n.memoizedState!==null)&&(Uc=H()),l&4&&(l=e.updateQueue,l!==null&&(e.updateQueue=null,_c(e,l)));break;case 22:if(l&512&&(Pe||n===null||Nt(n,n.return)),x=e.memoizedState!==null,_=n!==null&&n.memoizedState!==null,B=kn,Q=Pe,kn=B||x,Pe=Q||_,$t(t,e),Pe=Q,kn=B,Pt(e),t=e.stateNode,t._current=e,t._visibility&=-3,t._visibility|=t._pendingVisibility&2,l&8192&&(t._visibility=x?t._visibility&-2:t._visibility|1,x&&(t=kn||Pe,n===null||_||t||_l(e)),e.memoizedProps===null||e.memoizedProps.mode!=="manual"))e:for(n=null,t=e;;){if(t.tag===5||t.tag===26||t.tag===27){if(n===null){_=n=t;try{if(o=_.stateNode,x)c=o.style,typeof c.setProperty=="function"?c.setProperty("display","none","important"):c.display="none";else{d=_.stateNode,b=_.memoizedProps.style;var L=b!=null&&b.hasOwnProperty("display")?b.display:null;d.style.display=L==null||typeof L=="boolean"?"":(""+L).trim()}}catch(le){Ue(_,_.return,le)}}}else if(t.tag===6){if(n===null){_=t;try{_.stateNode.nodeValue=x?"":_.memoizedProps}catch(le){Ue(_,_.return,le)}}}else if((t.tag!==22&&t.tag!==23||t.memoizedState===null||t===e)&&t.child!==null){t.child.return=t,t=t.child;continue}if(t===e)break e;for(;t.sibling===null;){if(t.return===null||t.return===e)break e;n===t&&(n=null),t=t.return}n===t&&(n=null),t.sibling.return=t.return,t=t.sibling}l&4&&(l=e.updateQueue,l!==null&&(n=l.retryQueue,n!==null&&(l.retryQueue=null,_c(e,n))));break;case 19:$t(t,e),Pt(e),l&4&&(l=e.updateQueue,l!==null&&(e.updateQueue=null,_c(e,l)));break;case 21:break;default:$t(t,e),Pt(e)}}function Pt(e){var t=e.flags;if(t&2){try{if(e.tag!==27){e:{for(var n=e.return;n!==null;){if(kh(n)){var l=n;break e}n=n.return}throw Error(u(160))}switch(l.tag){case 27:var o=l.stateNode,c=Oc(e);Ji(e,c,o);break;case 5:var d=l.stateNode;l.flags&32&&(cl(d,""),l.flags&=-33);var b=Oc(e);Ji(e,b,d);break;case 3:case 4:var x=l.stateNode.containerInfo,_=Oc(e);Ac(e,_,x);break;default:throw Error(u(161))}}}catch(B){Ue(e,e.return,B)}e.flags&=-3}t&4096&&(e.flags&=-4097)}function jh(e){if(e.subtreeFlags&1024)for(e=e.child;e!==null;){var t=e;jh(t),t.tag===5&&t.flags&1024&&t.stateNode.reset(),e=e.sibling}}function Un(e,t){if(t.subtreeFlags&8772)for(t=t.child;t!==null;)qh(e,t.alternate,t),t=t.sibling}function _l(e){for(e=e.child;e!==null;){var t=e;switch(t.tag){case 0:case 11:case 14:case 15:oa(4,t,t.return),_l(t);break;case 1:Nt(t,t.return);var n=t.stateNode;typeof n.componentWillUnmount=="function"&&Rh(t,t.return,n),_l(t);break;case 26:case 27:case 5:Nt(t,t.return),_l(t);break;case 22:Nt(t,t.return),t.memoizedState===null&&_l(t);break;default:_l(t)}e=e.sibling}}function ua(e,t,n){for(n=n&&(t.subtreeFlags&8772)!==0,t=t.child;t!==null;){var l=t.alternate,o=e,c=t,d=c.flags;switch(c.tag){case 0:case 11:case 15:ua(o,c,n),Ar(4,c);break;case 1:if(ua(o,c,n),l=c,o=l.stateNode,typeof o.componentDidMount=="function")try{o.componentDidMount()}catch(_){Ue(l,l.return,_)}if(l=c,o=l.updateQueue,o!==null){var b=l.stateNode;try{var x=o.shared.hiddenCallbacks;if(x!==null)for(o.shared.hiddenCallbacks=null,o=0;o<x.length;o++)zh(x[o],b)}catch(_){Ue(l,l.return,_)}}n&&d&64&&wh(c),Xa(c,c.return);break;case 26:case 27:case 5:ua(o,c,n),n&&l===null&&d&4&&Mh(c),Xa(c,c.return);break;case 12:ua(o,c,n);break;case 13:ua(o,c,n),n&&d&4&&Bh(o,c);break;case 22:c.memoizedState===null&&ua(o,c,n),Xa(c,c.return);break;default:ua(o,c,n)}t=t.sibling}}function zc(e,t){var n=null;e!==null&&e.memoizedState!==null&&e.memoizedState.cachePool!==null&&(n=e.memoizedState.cachePool.pool),e=null,t.memoizedState!==null&&t.memoizedState.cachePool!==null&&(e=t.memoizedState.cachePool.pool),e!==n&&(e!=null&&e.refCount++,n!=null&&mr(n))}function Dc(e,t){e=null,t.alternate!==null&&(e=t.alternate.memoizedState.cache),t=t.memoizedState.cache,t!==e&&(t.refCount++,e!=null&&mr(e))}function ca(e,t,n,l){if(t.subtreeFlags&10256)for(t=t.child;t!==null;)Yh(e,t,n,l),t=t.sibling}function Yh(e,t,n,l){var o=t.flags;switch(t.tag){case 0:case 11:case 15:ca(e,t,n,l),o&2048&&Ar(9,t);break;case 3:ca(e,t,n,l),o&2048&&(e=null,t.alternate!==null&&(e=t.alternate.memoizedState.cache),t=t.memoizedState.cache,t!==e&&(t.refCount++,e!=null&&mr(e)));break;case 12:if(o&2048){ca(e,t,n,l),e=t.stateNode;try{var c=t.memoizedProps,d=c.id,b=c.onPostCommit;typeof b=="function"&&b(d,t.alternate===null?"mount":"update",e.passiveEffectDuration,-0)}catch(x){Ue(t,t.return,x)}}else ca(e,t,n,l);break;case 23:break;case 22:c=t.stateNode,t.memoizedState!==null?c._visibility&4?ca(e,t,n,l):Tr(e,t):c._visibility&4?ca(e,t,n,l):(c._visibility|=4,zl(e,t,n,l,(t.subtreeFlags&10256)!==0)),o&2048&&zc(t.alternate,t);break;case 24:ca(e,t,n,l),o&2048&&Dc(t.alternate,t);break;default:ca(e,t,n,l)}}function zl(e,t,n,l,o){for(o=o&&(t.subtreeFlags&10256)!==0,t=t.child;t!==null;){var c=e,d=t,b=n,x=l,_=d.flags;switch(d.tag){case 0:case 11:case 15:zl(c,d,b,x,o),Ar(8,d);break;case 23:break;case 22:var B=d.stateNode;d.memoizedState!==null?B._visibility&4?zl(c,d,b,x,o):Tr(c,d):(B._visibility|=4,zl(c,d,b,x,o)),o&&_&2048&&zc(d.alternate,d);break;case 24:zl(c,d,b,x,o),o&&_&2048&&Dc(d.alternate,d);break;default:zl(c,d,b,x,o)}t=t.sibling}}function Tr(e,t){if(t.subtreeFlags&10256)for(t=t.child;t!==null;){var n=e,l=t,o=l.flags;switch(l.tag){case 22:Tr(n,l),o&2048&&zc(l.alternate,l);break;case 24:Tr(n,l),o&2048&&Dc(l.alternate,l);break;default:Tr(n,l)}t=t.sibling}}var _r=8192;function Dl(e){if(e.subtreeFlags&_r)for(e=e.child;e!==null;)Xh(e),e=e.sibling}function Xh(e){switch(e.tag){case 26:Dl(e),e.flags&_r&&e.memoizedState!==null&&eg(ln,e.memoizedState,e.memoizedProps);break;case 5:Dl(e);break;case 3:case 4:var t=ln;ln=ho(e.stateNode.containerInfo),Dl(e),ln=t;break;case 22:e.memoizedState===null&&(t=e.alternate,t!==null&&t.memoizedState!==null?(t=_r,_r=16777216,Dl(e),_r=t):Dl(e));break;default:Dl(e)}}function Gh(e){var t=e.alternate;if(t!==null&&(e=t.child,e!==null)){t.child=null;do t=e.sibling,e.sibling=null,e=t;while(e!==null)}}function zr(e){var t=e.deletions;if((e.flags&16)!==0){if(t!==null)for(var n=0;n<t.length;n++){var l=t[n];dt=l,Zh(l,e)}Gh(e)}if(e.subtreeFlags&10256)for(e=e.child;e!==null;)Qh(e),e=e.sibling}function Qh(e){switch(e.tag){case 0:case 11:case 15:zr(e),e.flags&2048&&oa(9,e,e.return);break;case 3:zr(e);break;case 12:zr(e);break;case 22:var t=e.stateNode;e.memoizedState!==null&&t._visibility&4&&(e.return===null||e.return.tag!==13)?(t._visibility&=-5,Wi(e)):zr(e);break;default:zr(e)}}function Wi(e){var t=e.deletions;if((e.flags&16)!==0){if(t!==null)for(var n=0;n<t.length;n++){var l=t[n];dt=l,Zh(l,e)}Gh(e)}for(e=e.child;e!==null;){switch(t=e,t.tag){case 0:case 11:case 15:oa(8,t,t.return),Wi(t);break;case 22:n=t.stateNode,n._visibility&4&&(n._visibility&=-5,Wi(t));break;default:Wi(t)}e=e.sibling}}function Zh(e,t){for(;dt!==null;){var n=dt;switch(n.tag){case 0:case 11:case 15:oa(8,n,t);break;case 23:case 22:if(n.memoizedState!==null&&n.memoizedState.cachePool!==null){var l=n.memoizedState.cachePool.pool;l!=null&&l.refCount++}break;case 24:mr(n.memoizedState.cache)}if(l=n.child,l!==null)l.return=n,dt=l;else e:for(n=e;dt!==null;){l=dt;var o=l.sibling,c=l.return;if(Hh(l),l===n){dt=null;break e}if(o!==null){o.return=c,dt=o;break e}dt=c}}}function hb(e,t,n,l){this.tag=e,this.key=n,this.sibling=this.child=this.return=this.stateNode=this.type=this.elementType=null,this.index=0,this.refCleanup=this.ref=null,this.pendingProps=t,this.dependencies=this.memoizedState=this.updateQueue=this.memoizedProps=null,this.mode=l,this.subtreeFlags=this.flags=0,this.deletions=null,this.childLanes=this.lanes=0,this.alternate=null}function Ft(e,t,n,l){return new hb(e,t,n,l)}function wc(e){return e=e.prototype,!(!e||!e.isReactComponent)}function sa(e,t){var n=e.alternate;return n===null?(n=Ft(e.tag,t,e.key,e.mode),n.elementType=e.elementType,n.type=e.type,n.stateNode=e.stateNode,n.alternate=e,e.alternate=n):(n.pendingProps=t,n.type=e.type,n.flags=0,n.subtreeFlags=0,n.deletions=null),n.flags=e.flags&31457280,n.childLanes=e.childLanes,n.lanes=e.lanes,n.child=e.child,n.memoizedProps=e.memoizedProps,n.memoizedState=e.memoizedState,n.updateQueue=e.updateQueue,t=e.dependencies,n.dependencies=t===null?null:{lanes:t.lanes,firstContext:t.firstContext},n.sibling=e.sibling,n.index=e.index,n.ref=e.ref,n.refCleanup=e.refCleanup,n}function $h(e,t){e.flags&=31457282;var n=e.alternate;return n===null?(e.childLanes=0,e.lanes=t,e.child=null,e.subtreeFlags=0,e.memoizedProps=null,e.memoizedState=null,e.updateQueue=null,e.dependencies=null,e.stateNode=null):(e.childLanes=n.childLanes,e.lanes=n.lanes,e.child=n.child,e.subtreeFlags=0,e.deletions=null,e.memoizedProps=n.memoizedProps,e.memoizedState=n.memoizedState,e.updateQueue=n.updateQueue,e.type=n.type,t=n.dependencies,e.dependencies=t===null?null:{lanes:t.lanes,firstContext:t.firstContext}),e}function Ii(e,t,n,l,o,c){var d=0;if(l=e,typeof e=="function")wc(e)&&(d=1);else if(typeof e=="string")d=Wb(e,n,Dt.current)?26:e==="html"||e==="head"||e==="body"?27:5;else e:switch(e){case h:return Ga(n.children,o,c,t);case p:d=8,o|=24;break;case g:return e=Ft(12,n,t,o|2),e.elementType=g,e.lanes=c,e;case E:return e=Ft(13,n,t,o),e.elementType=E,e.lanes=c,e;case R:return e=Ft(19,n,t,o),e.elementType=R,e.lanes=c,e;case V:return Ph(n,o,c,t);default:if(typeof e=="object"&&e!==null)switch(e.$$typeof){case z:case w:d=10;break e;case M:d=9;break e;case A:d=11;break e;case q:d=14;break e;case N:d=16,l=null;break e}d=29,n=Error(u(130,e===null?"null":typeof e,"")),l=null}return t=Ft(d,n,t,o),t.elementType=e,t.type=l,t.lanes=c,t}function Ga(e,t,n,l){return e=Ft(7,e,l,t),e.lanes=n,e}function Ph(e,t,n,l){e=Ft(22,e,l,t),e.elementType=V,e.lanes=n;var o={_visibility:1,_pendingVisibility:1,_pendingMarkers:null,_retryCache:null,_transitions:null,_current:null,detach:function(){var c=o._current;if(c===null)throw Error(u(456));if((o._pendingVisibility&2)===0){var d=Wn(c,2);d!==null&&(o._pendingVisibility|=2,At(d,c,2))}},attach:function(){var c=o._current;if(c===null)throw Error(u(456));if((o._pendingVisibility&2)!==0){var d=Wn(c,2);d!==null&&(o._pendingVisibility&=-3,At(d,c,2))}}};return e.stateNode=o,e}function Rc(e,t,n){return e=Ft(6,e,null,t),e.lanes=n,e}function Mc(e,t,n){return t=Ft(4,e.children!==null?e.children:[],e.key,t),t.lanes=n,t.stateNode={containerInfo:e.containerInfo,pendingChildren:null,implementation:e.implementation},t}function qn(e){e.flags|=4}function Fh(e,t){if(t.type!=="stylesheet"||(t.state.loading&4)!==0)e.flags&=-16777217;else if(e.flags|=16777216,!k0(t)){if(t=Zt.current,t!==null&&((De&4194176)===De?pn!==null:(De&62914560)!==De&&(De&536870912)===0||t!==pn))throw fr=qu,dd;e.flags|=8192}}function eo(e,t){t!==null&&(e.flags|=4),e.flags&16384&&(t=e.tag!==22?hf():536870912,e.lanes|=t,Rl|=t)}function Dr(e,t){if(!we)switch(e.tailMode){case"hidden":t=e.tail;for(var n=null;t!==null;)t.alternate!==null&&(n=t),t=t.sibling;n===null?e.tail=null:n.sibling=null;break;case"collapsed":n=e.tail;for(var l=null;n!==null;)n.alternate!==null&&(l=n),n=n.sibling;l===null?t||e.tail===null?e.tail=null:e.tail.sibling=null:l.sibling=null}}function Ge(e){var t=e.alternate!==null&&e.alternate.child===e.child,n=0,l=0;if(t)for(var o=e.child;o!==null;)n|=o.lanes|o.childLanes,l|=o.subtreeFlags&31457280,l|=o.flags&31457280,o.return=e,o=o.sibling;else for(o=e.child;o!==null;)n|=o.lanes|o.childLanes,l|=o.subtreeFlags,l|=o.flags,o.return=e,o=o.sibling;return e.subtreeFlags|=l,e.childLanes=n,t}function mb(e,t,n){var l=t.pendingProps;switch(Nu(t),t.tag){case 16:case 15:case 0:case 11:case 7:case 8:case 12:case 9:case 14:return Ge(t),null;case 1:return Ge(t),null;case 3:return n=t.stateNode,l=null,e!==null&&(l=e.memoizedState.cache),t.memoizedState.cache!==l&&(t.flags|=2048),Cn(it),En(),n.pendingContext&&(n.context=n.pendingContext,n.pendingContext=null),(e===null||e.child===null)&&(or(t)?qn(t):e===null||e.memoizedState.isDehydrated&&(t.flags&256)===0||(t.flags|=1024,an!==null&&(Bc(an),an=null))),Ge(t),null;case 26:return n=t.memoizedState,e===null?(qn(t),n!==null?(Ge(t),Fh(t,n)):(Ge(t),t.flags&=-16777217)):n?n!==e.memoizedState?(qn(t),Ge(t),Fh(t,n)):(Ge(t),t.flags&=-16777217):(e.memoizedProps!==l&&qn(t),Ge(t),t.flags&=-16777217),null;case 27:nl(t),n=hn.current;var o=t.type;if(e!==null&&t.stateNode!=null)e.memoizedProps!==l&&qn(t);else{if(!l){if(t.stateNode===null)throw Error(u(166));return Ge(t),null}e=Dt.current,or(t)?sd(t):(e=T0(o,l,n),t.stateNode=e,qn(t))}return Ge(t),null;case 5:if(nl(t),n=t.type,e!==null&&t.stateNode!=null)e.memoizedProps!==l&&qn(t);else{if(!l){if(t.stateNode===null)throw Error(u(166));return Ge(t),null}if(e=Dt.current,or(t))sd(t);else{switch(o=fo(hn.current),e){case 1:e=o.createElementNS("http://www.w3.org/2000/svg",n);break;case 2:e=o.createElementNS("http://www.w3.org/1998/Math/MathML",n);break;default:switch(n){case"svg":e=o.createElementNS("http://www.w3.org/2000/svg",n);break;case"math":e=o.createElementNS("http://www.w3.org/1998/Math/MathML",n);break;case"script":e=o.createElement("div"),e.innerHTML="<script><\/script>",e=e.removeChild(e.firstChild);break;case"select":e=typeof l.is=="string"?o.createElement("select",{is:l.is}):o.createElement("select"),l.multiple?e.multiple=!0:l.size&&(e.size=l.size);break;default:e=typeof l.is=="string"?o.createElement(n,{is:l.is}):o.createElement(n)}}e[yt]=t,e[wt]=l;e:for(o=t.child;o!==null;){if(o.tag===5||o.tag===6)e.appendChild(o.stateNode);else if(o.tag!==4&&o.tag!==27&&o.child!==null){o.child.return=o,o=o.child;continue}if(o===t)break e;for(;o.sibling===null;){if(o.return===null||o.return===t)break e;o=o.return}o.sibling.return=o.return,o=o.sibling}t.stateNode=e;e:switch(bt(e,n,l),n){case"button":case"input":case"select":case"textarea":e=!!l.autoFocus;break e;case"img":e=!0;break e;default:e=!1}e&&qn(t)}}return Ge(t),t.flags&=-16777217,null;case 6:if(e&&t.stateNode!=null)e.memoizedProps!==l&&qn(t);else{if(typeof l!="string"&&t.stateNode===null)throw Error(u(166));if(e=hn.current,or(t)){if(e=t.stateNode,n=t.memoizedProps,l=null,o=Ot,o!==null)switch(o.tag){case 27:case 5:l=o.memoizedProps}e[yt]=t,e=!!(e.nodeValue===n||l!==null&&l.suppressHydrationWarning===!0||y0(e.nodeValue,n)),e||Na(t)}else e=fo(e).createTextNode(l),e[yt]=t,t.stateNode=e}return Ge(t),null;case 13:if(l=t.memoizedState,e===null||e.memoizedState!==null&&e.memoizedState.dehydrated!==null){if(o=or(t),l!==null&&l.dehydrated!==null){if(e===null){if(!o)throw Error(u(318));if(o=t.memoizedState,o=o!==null?o.dehydrated:null,!o)throw Error(u(317));o[yt]=t}else ur(),(t.flags&128)===0&&(t.memoizedState=null),t.flags|=4;Ge(t),o=!1}else an!==null&&(Bc(an),an=null),o=!0;if(!o)return t.flags&256?(Dn(t),t):(Dn(t),null)}if(Dn(t),(t.flags&128)!==0)return t.lanes=n,t;if(n=l!==null,e=e!==null&&e.memoizedState!==null,n){l=t.child,o=null,l.alternate!==null&&l.alternate.memoizedState!==null&&l.alternate.memoizedState.cachePool!==null&&(o=l.alternate.memoizedState.cachePool.pool);var c=null;l.memoizedState!==null&&l.memoizedState.cachePool!==null&&(c=l.memoizedState.cachePool.pool),c!==o&&(l.flags|=2048)}return n!==e&&n&&(t.child.flags|=8192),eo(t,t.updateQueue),Ge(t),null;case 4:return En(),e===null&&Kc(t.stateNode.containerInfo),Ge(t),null;case 10:return Cn(t.type),Ge(t),null;case 19:if(Me(rt),o=t.memoizedState,o===null)return Ge(t),null;if(l=(t.flags&128)!==0,c=o.rendering,c===null)if(l)Dr(o,!1);else{if(Fe!==0||e!==null&&(e.flags&128)!==0)for(e=t.child;e!==null;){if(c=Hi(e),c!==null){for(t.flags|=128,Dr(o,!1),e=c.updateQueue,t.updateQueue=e,eo(t,e),t.subtreeFlags=0,e=n,n=t.child;n!==null;)$h(n,e),n=n.sibling;return Ne(rt,rt.current&1|2),t.child}e=e.sibling}o.tail!==null&&H()>to&&(t.flags|=128,l=!0,Dr(o,!1),t.lanes=4194304)}else{if(!l)if(e=Hi(c),e!==null){if(t.flags|=128,l=!0,e=e.updateQueue,t.updateQueue=e,eo(t,e),Dr(o,!0),o.tail===null&&o.tailMode==="hidden"&&!c.alternate&&!we)return Ge(t),null}else 2*H()-o.renderingStartTime>to&&n!==536870912&&(t.flags|=128,l=!0,Dr(o,!1),t.lanes=4194304);o.isBackwards?(c.sibling=t.child,t.child=c):(e=o.last,e!==null?e.sibling=c:t.child=c,o.last=c)}return o.tail!==null?(t=o.tail,o.rendering=t,o.tail=t.sibling,o.renderingStartTime=H(),t.sibling=null,e=rt.current,Ne(rt,l?e&1|2:e&1),t):(Ge(t),null);case 22:case 23:return Dn(t),Lu(),l=t.memoizedState!==null,e!==null?e.memoizedState!==null!==l&&(t.flags|=8192):l&&(t.flags|=8192),l?(n&536870912)!==0&&(t.flags&128)===0&&(Ge(t),t.subtreeFlags&6&&(t.flags|=8192)):Ge(t),n=t.updateQueue,n!==null&&eo(t,n.retryQueue),n=null,e!==null&&e.memoizedState!==null&&e.memoizedState.cachePool!==null&&(n=e.memoizedState.cachePool.pool),l=null,t.memoizedState!==null&&t.memoizedState.cachePool!==null&&(l=t.memoizedState.cachePool.pool),l!==n&&(t.flags|=2048),e!==null&&Me(qa),null;case 24:return n=null,e!==null&&(n=e.memoizedState.cache),t.memoizedState.cache!==n&&(t.flags|=2048),Cn(it),Ge(t),null;case 25:return null}throw Error(u(156,t.tag))}function pb(e,t){switch(Nu(t),t.tag){case 1:return e=t.flags,e&65536?(t.flags=e&-65537|128,t):null;case 3:return Cn(it),En(),e=t.flags,(e&65536)!==0&&(e&128)===0?(t.flags=e&-65537|128,t):null;case 26:case 27:case 5:return nl(t),null;case 13:if(Dn(t),e=t.memoizedState,e!==null&&e.dehydrated!==null){if(t.alternate===null)throw Error(u(340));ur()}return e=t.flags,e&65536?(t.flags=e&-65537|128,t):null;case 19:return Me(rt),null;case 4:return En(),null;case 10:return Cn(t.type),null;case 22:case 23:return Dn(t),Lu(),e!==null&&Me(qa),e=t.flags,e&65536?(t.flags=e&-65537|128,t):null;case 24:return Cn(it),null;case 25:return null;default:return null}}function Kh(e,t){switch(Nu(t),t.tag){case 3:Cn(it),En();break;case 26:case 27:case 5:nl(t);break;case 4:En();break;case 13:Dn(t);break;case 19:Me(rt);break;case 10:Cn(t.type);break;case 22:case 23:Dn(t),Lu(),e!==null&&Me(qa);break;case 24:Cn(it)}}var vb={getCacheForType:function(e){var t=xt(it),n=t.data.get(e);return n===void 0&&(n=e(),t.data.set(e,n)),n}},bb=typeof WeakMap=="function"?WeakMap:Map,Qe=0,He=null,Ee=null,De=0,Le=0,qt=null,Hn=!1,wl=!1,Cc=!1,Ln=0,Fe=0,fa=0,Qa=0,kc=0,Kt=0,Rl=0,wr=null,bn=null,Nc=!1,Uc=0,to=1/0,no=null,da=null,ao=!1,Za=null,Rr=0,qc=0,Hc=null,Mr=0,Lc=null;function Ht(){if((Qe&2)!==0&&De!==0)return De&-De;if(G.T!==null){var e=Sl;return e!==0?e:Zc()}return bf()}function Jh(){Kt===0&&(Kt=(De&536870912)===0||we?df():536870912);var e=Zt.current;return e!==null&&(e.flags|=32),Kt}function At(e,t,n){(e===He&&Le===2||e.cancelPendingCommit!==null)&&(Ml(e,0),Bn(e,De,Kt,!1)),Fl(e,n),((Qe&2)===0||e!==He)&&(e===He&&((Qe&2)===0&&(Qa|=n),Fe===4&&Bn(e,De,Kt,!1)),gn(e))}function Wh(e,t,n){if((Qe&6)!==0)throw Error(u(327));var l=!n&&(t&60)===0&&(t&e.expiredLanes)===0||Pl(e,t),o=l?xb(e,t):Yc(e,t,!0),c=l;do{if(o===0){wl&&!l&&Bn(e,t,0,!1);break}else if(o===6)Bn(e,t,0,!Hn);else{if(n=e.current.alternate,c&&!gb(n)){o=Yc(e,t,!1),c=!1;continue}if(o===2){if(c=t,e.errorRecoveryDisabledLanes&c)var d=0;else d=e.pendingLanes&-536870913,d=d!==0?d:d&536870912?536870912:0;if(d!==0){t=d;e:{var b=e;o=wr;var x=b.current.memoizedState.isDehydrated;if(x&&(Ml(b,d).flags|=256),d=Yc(b,d,!1),d!==2){if(Cc&&!x){b.errorRecoveryDisabledLanes|=c,Qa|=c,o=4;break e}c=bn,bn=o,c!==null&&Bc(c)}o=d}if(c=!1,o!==2)continue}}if(o===1){Ml(e,0),Bn(e,t,0,!0);break}e:{switch(l=e,o){case 0:case 1:throw Error(u(345));case 4:if((t&4194176)===t){Bn(l,t,Kt,!Hn);break e}break;case 2:bn=null;break;case 3:case 5:break;default:throw Error(u(329))}if(l.finishedWork=n,l.finishedLanes=t,(t&62914560)===t&&(c=Uc+300-H(),10<c)){if(Bn(l,t,Kt,!Hn),vi(l,0)!==0)break e;l.timeoutHandle=E0(Ih.bind(null,l,n,bn,no,Nc,t,Kt,Qa,Rl,Hn,2,-0,0),c);break e}Ih(l,n,bn,no,Nc,t,Kt,Qa,Rl,Hn,0,-0,0)}}break}while(!0);gn(e)}function Bc(e){bn===null?bn=e:bn.push.apply(bn,e)}function Ih(e,t,n,l,o,c,d,b,x,_,B,Q,k){var L=t.subtreeFlags;if((L&8192||(L&16785408)===16785408)&&(Hr={stylesheets:null,count:0,unsuspend:Ib},Xh(t),t=tg(),t!==null)){e.cancelPendingCommit=t(i0.bind(null,e,n,l,o,d,b,x,1,Q,k)),Bn(e,c,d,!_);return}i0(e,n,l,o,d,b,x,B,Q,k)}function gb(e){for(var t=e;;){var n=t.tag;if((n===0||n===11||n===15)&&t.flags&16384&&(n=t.updateQueue,n!==null&&(n=n.stores,n!==null)))for(var l=0;l<n.length;l++){var o=n[l],c=o.getSnapshot;o=o.value;try{if(!kt(c(),o))return!1}catch{return!1}}if(n=t.child,t.subtreeFlags&16384&&n!==null)n.return=t,t=n;else{if(t===e)break;for(;t.sibling===null;){if(t.return===null||t.return===e)return!0;t=t.return}t.sibling.return=t.return,t=t.sibling}}return!0}function Bn(e,t,n,l){t&=~kc,t&=~Qa,e.suspendedLanes|=t,e.pingedLanes&=~t,l&&(e.warmLanes|=t),l=e.expirationTimes;for(var o=t;0<o;){var c=31-Et(o),d=1<<c;l[c]=-1,o&=~d}n!==0&&mf(e,n,t)}function lo(){return(Qe&6)===0?(Cr(0),!1):!0}function Vc(){if(Ee!==null){if(Le===0)var e=Ee.return;else e=Ee,Mn=ja=null,Zu(e),yl=null,dr=0,e=Ee;for(;e!==null;)Kh(e.alternate,e),e=e.return;Ee=null}}function Ml(e,t){e.finishedWork=null,e.finishedLanes=0;var n=e.timeoutHandle;n!==-1&&(e.timeoutHandle=-1,Hb(n)),n=e.cancelPendingCommit,n!==null&&(e.cancelPendingCommit=null,n()),Vc(),He=e,Ee=n=sa(e.current,null),De=t,Le=0,qt=null,Hn=!1,wl=Pl(e,t),Cc=!1,Rl=Kt=kc=Qa=fa=Fe=0,bn=wr=null,Nc=!1,(t&8)!==0&&(t|=t&32);var l=e.entangledLanes;if(l!==0)for(e=e.entanglements,l&=t;0<l;){var o=31-Et(l),c=1<<o;t|=e[o],l&=~c}return Ln=t,Di(),n}function e0(e,t){be=null,G.H=vn,t===sr?(t=pd(),Le=3):t===dd?(t=pd(),Le=4):Le=t===hh?8:t!==null&&typeof t=="object"&&typeof t.then=="function"?6:1,qt=t,Ee===null&&(Fe=1,Pi(e,Xt(t,e.current)))}function t0(){var e=G.H;return G.H=vn,e===null?vn:e}function n0(){var e=G.A;return G.A=vb,e}function jc(){Fe=4,Hn||(De&4194176)!==De&&Zt.current!==null||(wl=!0),(fa&134217727)===0&&(Qa&134217727)===0||He===null||Bn(He,De,Kt,!1)}function Yc(e,t,n){var l=Qe;Qe|=2;var o=t0(),c=n0();(He!==e||De!==t)&&(no=null,Ml(e,t)),t=!1;var d=Fe;e:do try{if(Le!==0&&Ee!==null){var b=Ee,x=qt;switch(Le){case 8:Vc(),d=6;break e;case 3:case 2:case 6:Zt.current===null&&(t=!0);var _=Le;if(Le=0,qt=null,Cl(e,b,x,_),n&&wl){d=0;break e}break;default:_=Le,Le=0,qt=null,Cl(e,b,x,_)}}yb(),d=Fe;break}catch(B){e0(e,B)}while(!0);return t&&e.shellSuspendCounter++,Mn=ja=null,Qe=l,G.H=o,G.A=c,Ee===null&&(He=null,De=0,Di()),d}function yb(){for(;Ee!==null;)a0(Ee)}function xb(e,t){var n=Qe;Qe|=2;var l=t0(),o=n0();He!==e||De!==t?(no=null,to=H()+500,Ml(e,t)):wl=Pl(e,t);e:do try{if(Le!==0&&Ee!==null){t=Ee;var c=qt;t:switch(Le){case 1:Le=0,qt=null,Cl(e,t,c,1);break;case 2:if(hd(c)){Le=0,qt=null,l0(t);break}t=function(){Le===2&&He===e&&(Le=7),gn(e)},c.then(t,t);break e;case 3:Le=7;break e;case 4:Le=5;break e;case 7:hd(c)?(Le=0,qt=null,l0(t)):(Le=0,qt=null,Cl(e,t,c,7));break;case 5:var d=null;switch(Ee.tag){case 26:d=Ee.memoizedState;case 5:case 27:var b=Ee;if(!d||k0(d)){Le=0,qt=null;var x=b.sibling;if(x!==null)Ee=x;else{var _=b.return;_!==null?(Ee=_,ro(_)):Ee=null}break t}}Le=0,qt=null,Cl(e,t,c,5);break;case 6:Le=0,qt=null,Cl(e,t,c,6);break;case 8:Vc(),Fe=6;break e;default:throw Error(u(462))}}Sb();break}catch(B){e0(e,B)}while(!0);return Mn=ja=null,G.H=l,G.A=o,Qe=n,Ee!==null?0:(He=null,De=0,Di(),Fe)}function Sb(){for(;Ee!==null&&!S();)a0(Ee)}function a0(e){var t=Th(e.alternate,e,Ln);e.memoizedProps=e.pendingProps,t===null?ro(e):Ee=t}function l0(e){var t=e,n=t.alternate;switch(t.tag){case 15:case 0:t=yh(n,t,t.pendingProps,t.type,void 0,De);break;case 11:t=yh(n,t,t.pendingProps,t.type.render,t.ref,De);break;case 5:Zu(t);default:Kh(n,t),t=Ee=$h(t,Ln),t=Th(n,t,Ln)}e.memoizedProps=e.pendingProps,t===null?ro(e):Ee=t}function Cl(e,t,n,l){Mn=ja=null,Zu(t),yl=null,dr=0;var o=t.return;try{if(cb(e,o,t,n,De)){Fe=1,Pi(e,Xt(n,e.current)),Ee=null;return}}catch(c){if(o!==null)throw Ee=o,c;Fe=1,Pi(e,Xt(n,e.current)),Ee=null;return}t.flags&32768?(we||l===1?e=!0:wl||(De&536870912)!==0?e=!1:(Hn=e=!0,(l===2||l===3||l===6)&&(l=Zt.current,l!==null&&l.tag===13&&(l.flags|=16384))),r0(t,e)):ro(t)}function ro(e){var t=e;do{if((t.flags&32768)!==0){r0(t,Hn);return}e=t.return;var n=mb(t.alternate,t,Ln);if(n!==null){Ee=n;return}if(t=t.sibling,t!==null){Ee=t;return}Ee=t=e}while(t!==null);Fe===0&&(Fe=5)}function r0(e,t){do{var n=pb(e.alternate,e);if(n!==null){n.flags&=32767,Ee=n;return}if(n=e.return,n!==null&&(n.flags|=32768,n.subtreeFlags=0,n.deletions=null),!t&&(e=e.sibling,e!==null)){Ee=e;return}Ee=e=n}while(e!==null);Fe=6,Ee=null}function i0(e,t,n,l,o,c,d,b,x,_){var B=G.T,Q=ne.p;try{ne.p=2,G.T=null,Eb(e,t,n,l,Q,o,c,d,b,x,_)}finally{G.T=B,ne.p=Q}}function Eb(e,t,n,l,o,c,d,b){do kl();while(Za!==null);if((Qe&6)!==0)throw Error(u(327));var x=e.finishedWork;if(l=e.finishedLanes,x===null)return null;if(e.finishedWork=null,e.finishedLanes=0,x===e.current)throw Error(u(177));e.callbackNode=null,e.callbackPriority=0,e.cancelPendingCommit=null;var _=x.lanes|x.childLanes;if(_|=Mu,ev(e,l,_,c,d,b),e===He&&(Ee=He=null,De=0),(x.subtreeFlags&10256)===0&&(x.flags&10256)===0||ao||(ao=!0,qc=_,Hc=n,_b(I,function(){return kl(),null})),n=(x.flags&15990)!==0,(x.subtreeFlags&15990)!==0||n?(n=G.T,G.T=null,c=ne.p,ne.p=2,d=Qe,Qe|=4,fb(e,x),Vh(x,e),Zv(es,e.containerInfo),go=!!Ic,es=Ic=null,e.current=x,qh(e,x.alternate,x),U(),Qe=d,ne.p=c,G.T=n):e.current=x,ao?(ao=!1,Za=e,Rr=l):o0(e,_),_=e.pendingLanes,_===0&&(da=null),uu(x.stateNode),gn(e),t!==null)for(o=e.onRecoverableError,x=0;x<t.length;x++)_=t[x],o(_.value,{componentStack:_.stack});return(Rr&3)!==0&&kl(),_=e.pendingLanes,(l&4194218)!==0&&(_&42)!==0?e===Lc?Mr++:(Mr=0,Lc=e):Mr=0,Cr(0),null}function o0(e,t){(e.pooledCacheLanes&=t)===0&&(t=e.pooledCache,t!=null&&(e.pooledCache=null,mr(t)))}function kl(){if(Za!==null){var e=Za,t=qc;qc=0;var n=vf(Rr),l=G.T,o=ne.p;try{if(ne.p=32>n?32:n,G.T=null,Za===null)var c=!1;else{n=Hc,Hc=null;var d=Za,b=Rr;if(Za=null,Rr=0,(Qe&6)!==0)throw Error(u(331));var x=Qe;if(Qe|=4,Qh(d.current),Yh(d,d.current,b,n),Qe=x,Cr(0,!1),mt&&typeof mt.onPostCommitFiberRoot=="function")try{mt.onPostCommitFiberRoot(On,d)}catch{}c=!0}return c}finally{ne.p=o,G.T=l,o0(e,t)}}return!1}function u0(e,t,n){t=Xt(n,t),t=ic(e.stateNode,t,2),e=ia(e,t,2),e!==null&&(Fl(e,2),gn(e))}function Ue(e,t,n){if(e.tag===3)u0(e,e,n);else for(;t!==null;){if(t.tag===3){u0(t,e,n);break}else if(t.tag===1){var l=t.stateNode;if(typeof t.type.getDerivedStateFromError=="function"||typeof l.componentDidCatch=="function"&&(da===null||!da.has(l))){e=Xt(n,e),n=fh(2),l=ia(t,n,2),l!==null&&(dh(n,l,t,e),Fl(l,2),gn(l));break}}t=t.return}}function Xc(e,t,n){var l=e.pingCache;if(l===null){l=e.pingCache=new bb;var o=new Set;l.set(t,o)}else o=l.get(t),o===void 0&&(o=new Set,l.set(t,o));o.has(n)||(Cc=!0,o.add(n),e=Ob.bind(null,e,t,n),t.then(e,e))}function Ob(e,t,n){var l=e.pingCache;l!==null&&l.delete(t),e.pingedLanes|=e.suspendedLanes&n,e.warmLanes&=~n,He===e&&(De&n)===n&&(Fe===4||Fe===3&&(De&62914560)===De&&300>H()-Uc?(Qe&2)===0&&Ml(e,0):kc|=n,Rl===De&&(Rl=0)),gn(e)}function c0(e,t){t===0&&(t=hf()),e=Wn(e,t),e!==null&&(Fl(e,t),gn(e))}function Ab(e){var t=e.memoizedState,n=0;t!==null&&(n=t.retryLane),c0(e,n)}function Tb(e,t){var n=0;switch(e.tag){case 13:var l=e.stateNode,o=e.memoizedState;o!==null&&(n=o.retryLane);break;case 19:l=e.stateNode;break;case 22:l=e.stateNode._retryCache;break;default:throw Error(u(314))}l!==null&&l.delete(t),c0(e,n)}function _b(e,t){return di(e,t)}var io=null,Nl=null,Gc=!1,oo=!1,Qc=!1,$a=0;function gn(e){e!==Nl&&e.next===null&&(Nl===null?io=Nl=e:Nl=Nl.next=e),oo=!0,Gc||(Gc=!0,Db(zb))}function Cr(e,t){if(!Qc&&oo){Qc=!0;do for(var n=!1,l=io;l!==null;){if(e!==0){var o=l.pendingLanes;if(o===0)var c=0;else{var d=l.suspendedLanes,b=l.pingedLanes;c=(1<<31-Et(42|e)+1)-1,c&=o&~(d&~b),c=c&201326677?c&201326677|1:c?c|2:0}c!==0&&(n=!0,d0(l,c))}else c=De,c=vi(l,l===He?c:0),(c&3)===0||Pl(l,c)||(n=!0,d0(l,c));l=l.next}while(n);Qc=!1}}function zb(){oo=Gc=!1;var e=0;$a!==0&&(qb()&&(e=$a),$a=0);for(var t=H(),n=null,l=io;l!==null;){var o=l.next,c=s0(l,t);c===0?(l.next=null,n===null?io=o:n.next=o,o===null&&(Nl=n)):(n=l,(e!==0||(c&3)!==0)&&(oo=!0)),l=o}Cr(e)}function s0(e,t){for(var n=e.suspendedLanes,l=e.pingedLanes,o=e.expirationTimes,c=e.pendingLanes&-62914561;0<c;){var d=31-Et(c),b=1<<d,x=o[d];x===-1?((b&n)===0||(b&l)!==0)&&(o[d]=Ip(b,t)):x<=t&&(e.expiredLanes|=b),c&=~b}if(t=He,n=De,n=vi(e,e===t?n:0),l=e.callbackNode,n===0||e===t&&Le===2||e.cancelPendingCommit!==null)return l!==null&&l!==null&&al(l),e.callbackNode=null,e.callbackPriority=0;if((n&3)===0||Pl(e,n)){if(t=n&-n,t===e.callbackPriority)return t;switch(l!==null&&al(l),vf(n)){case 2:case 8:n=Z;break;case 32:n=I;break;case 268435456:n=Xe;break;default:n=I}return l=f0.bind(null,e),n=di(n,l),e.callbackPriority=t,e.callbackNode=n,t}return l!==null&&l!==null&&al(l),e.callbackPriority=2,e.callbackNode=null,2}function f0(e,t){var n=e.callbackNode;if(kl()&&e.callbackNode!==n)return null;var l=De;return l=vi(e,e===He?l:0),l===0?null:(Wh(e,l,t),s0(e,H()),e.callbackNode!=null&&e.callbackNode===n?f0.bind(null,e):null)}function d0(e,t){if(kl())return null;Wh(e,t,!0)}function Db(e){Lb(function(){(Qe&6)!==0?di(P,e):e()})}function Zc(){return $a===0&&($a=df()),$a}function h0(e){return e==null||typeof e=="symbol"||typeof e=="boolean"?null:typeof e=="function"?e:Si(""+e)}function m0(e,t){var n=t.ownerDocument.createElement("input");return n.name=t.name,n.value=t.value,e.id&&n.setAttribute("form",e.id),t.parentNode.insertBefore(n,t),e=new FormData(e),n.parentNode.removeChild(n),e}function wb(e,t,n,l,o){if(t==="submit"&&n&&n.stateNode===o){var c=h0((o[wt]||null).action),d=l.submitter;d&&(t=(t=d[wt]||null)?h0(t.formAction):d.getAttribute("formAction"),t!==null&&(c=t,d=null));var b=new Ti("action","action",null,l,o);e.push({event:b,listeners:[{instance:null,listener:function(){if(l.defaultPrevented){if($a!==0){var x=d?m0(o,d):new FormData(o);tc(n,{pending:!0,data:x,method:o.method,action:c},null,x)}}else typeof c=="function"&&(b.preventDefault(),x=d?m0(o,d):new FormData(o),tc(n,{pending:!0,data:x,method:o.method,action:c},c,x))},currentTarget:o}]})}}for(var $c=0;$c<id.length;$c++){var Pc=id[$c],Rb=Pc.toLowerCase(),Mb=Pc[0].toUpperCase()+Pc.slice(1);nn(Rb,"on"+Mb)}nn(td,"onAnimationEnd"),nn(nd,"onAnimationIteration"),nn(ad,"onAnimationStart"),nn("dblclick","onDoubleClick"),nn("focusin","onFocus"),nn("focusout","onBlur"),nn(Pv,"onTransitionRun"),nn(Fv,"onTransitionStart"),nn(Kv,"onTransitionCancel"),nn(ld,"onTransitionEnd"),ol("onMouseEnter",["mouseout","mouseover"]),ol("onMouseLeave",["mouseout","mouseover"]),ol("onPointerEnter",["pointerout","pointerover"]),ol("onPointerLeave",["pointerout","pointerover"]),Da("onChange","change click focusin focusout input keydown keyup selectionchange".split(" ")),Da("onSelect","focusout contextmenu dragend focusin keydown keyup mousedown mouseup selectionchange".split(" ")),Da("onBeforeInput",["compositionend","keypress","textInput","paste"]),Da("onCompositionEnd","compositionend focusout keydown keypress keyup mousedown".split(" ")),Da("onCompositionStart","compositionstart focusout keydown keypress keyup mousedown".split(" ")),Da("onCompositionUpdate","compositionupdate focusout keydown keypress keyup mousedown".split(" "));var kr="abort canplay canplaythrough durationchange emptied encrypted ended error loadeddata loadedmetadata loadstart pause play playing progress ratechange resize seeked seeking stalled suspend timeupdate volumechange waiting".split(" "),Cb=new Set("beforetoggle cancel close invalid load scroll scrollend toggle".split(" ").concat(kr));function p0(e,t){t=(t&4)!==0;for(var n=0;n<e.length;n++){var l=e[n],o=l.event;l=l.listeners;e:{var c=void 0;if(t)for(var d=l.length-1;0<=d;d--){var b=l[d],x=b.instance,_=b.currentTarget;if(b=b.listener,x!==c&&o.isPropagationStopped())break e;c=b,o.currentTarget=_;try{c(o)}catch(B){$i(B)}o.currentTarget=null,c=x}else for(d=0;d<l.length;d++){if(b=l[d],x=b.instance,_=b.currentTarget,b=b.listener,x!==c&&o.isPropagationStopped())break e;c=b,o.currentTarget=_;try{c(o)}catch(B){$i(B)}o.currentTarget=null,c=x}}}}function _e(e,t){var n=t[su];n===void 0&&(n=t[su]=new Set);var l=e+"__bubble";n.has(l)||(v0(t,e,2,!1),n.add(l))}function Fc(e,t,n){var l=0;t&&(l|=4),v0(n,e,l,t)}var uo="_reactListening"+Math.random().toString(36).slice(2);function Kc(e){if(!e[uo]){e[uo]=!0,yf.forEach(function(n){n!=="selectionchange"&&(Cb.has(n)||Fc(n,!1,e),Fc(n,!0,e))});var t=e.nodeType===9?e:e.ownerDocument;t===null||t[uo]||(t[uo]=!0,Fc("selectionchange",!1,t))}}function v0(e,t,n,l){switch(B0(t)){case 2:var o=lg;break;case 8:o=rg;break;default:o=cs}n=o.bind(null,t,n,e),o=void 0,!gu||t!=="touchstart"&&t!=="touchmove"&&t!=="wheel"||(o=!0),l?o!==void 0?e.addEventListener(t,n,{capture:!0,passive:o}):e.addEventListener(t,n,!0):o!==void 0?e.addEventListener(t,n,{passive:o}):e.addEventListener(t,n,!1)}function Jc(e,t,n,l,o){var c=l;if((t&1)===0&&(t&2)===0&&l!==null)e:for(;;){if(l===null)return;var d=l.tag;if(d===3||d===4){var b=l.stateNode.containerInfo;if(b===o||b.nodeType===8&&b.parentNode===o)break;if(d===4)for(d=l.return;d!==null;){var x=d.tag;if((x===3||x===4)&&(x=d.stateNode.containerInfo,x===o||x.nodeType===8&&x.parentNode===o))return;d=d.return}for(;b!==null;){if(d=za(b),d===null)return;if(x=d.tag,x===5||x===6||x===26||x===27){l=c=d;continue e}b=b.parentNode}}l=l.return}Mf(function(){var _=c,B=vu(n),Q=[];e:{var k=rd.get(e);if(k!==void 0){var L=Ti,le=e;switch(e){case"keypress":if(Oi(n)===0)break e;case"keydown":case"keyup":L=Tv;break;case"focusin":le="focus",L=Eu;break;case"focusout":le="blur",L=Eu;break;case"beforeblur":case"afterblur":L=Eu;break;case"click":if(n.button===2)break e;case"auxclick":case"dblclick":case"mousedown":case"mousemove":case"mouseup":case"mouseout":case"mouseover":case"contextmenu":L=Nf;break;case"drag":case"dragend":case"dragenter":case"dragexit":case"dragleave":case"dragover":case"dragstart":case"drop":L=hv;break;case"touchcancel":case"touchend":case"touchmove":case"touchstart":L=Dv;break;case td:case nd:case ad:L=vv;break;case ld:L=Rv;break;case"scroll":case"scrollend":L=fv;break;case"wheel":L=Cv;break;case"copy":case"cut":case"paste":L=gv;break;case"gotpointercapture":case"lostpointercapture":case"pointercancel":case"pointerdown":case"pointermove":case"pointerout":case"pointerover":case"pointerup":L=qf;break;case"toggle":case"beforetoggle":L=Nv}var fe=(t&4)!==0,Ke=!fe&&(e==="scroll"||e==="scrollend"),D=fe?k!==null?k+"Capture":null:k;fe=[];for(var O=_,C;O!==null;){var j=O;if(C=j.stateNode,j=j.tag,j!==5&&j!==26&&j!==27||C===null||D===null||(j=Wl(O,D),j!=null&&fe.push(Nr(O,j,C))),Ke)break;O=O.return}0<fe.length&&(k=new L(k,le,null,n,B),Q.push({event:k,listeners:fe}))}}if((t&7)===0){e:{if(k=e==="mouseover"||e==="pointerover",L=e==="mouseout"||e==="pointerout",k&&n!==pu&&(le=n.relatedTarget||n.fromElement)&&(za(le)||le[ll]))break e;if((L||k)&&(k=B.window===B?B:(k=B.ownerDocument)?k.defaultView||k.parentWindow:window,L?(le=n.relatedTarget||n.toElement,L=_,le=le?za(le):null,le!==null&&(Ke=Ve(le),fe=le.tag,le!==Ke||fe!==5&&fe!==27&&fe!==6)&&(le=null)):(L=null,le=_),L!==le)){if(fe=Nf,j="onMouseLeave",D="onMouseEnter",O="mouse",(e==="pointerout"||e==="pointerover")&&(fe=qf,j="onPointerLeave",D="onPointerEnter",O="pointer"),Ke=L==null?k:Jl(L),C=le==null?k:Jl(le),k=new fe(j,O+"leave",L,n,B),k.target=Ke,k.relatedTarget=C,j=null,za(B)===_&&(fe=new fe(D,O+"enter",le,n,B),fe.target=C,fe.relatedTarget=Ke,j=fe),Ke=j,L&&le)t:{for(fe=L,D=le,O=0,C=fe;C;C=Ul(C))O++;for(C=0,j=D;j;j=Ul(j))C++;for(;0<O-C;)fe=Ul(fe),O--;for(;0<C-O;)D=Ul(D),C--;for(;O--;){if(fe===D||D!==null&&fe===D.alternate)break t;fe=Ul(fe),D=Ul(D)}fe=null}else fe=null;L!==null&&b0(Q,k,L,fe,!1),le!==null&&Ke!==null&&b0(Q,Ke,le,fe,!0)}}e:{if(k=_?Jl(_):window,L=k.nodeName&&k.nodeName.toLowerCase(),L==="select"||L==="input"&&k.type==="file")var te=Gf;else if(Yf(k))if(Qf)te=Gv;else{te=Yv;var xe=jv}else L=k.nodeName,!L||L.toLowerCase()!=="input"||k.type!=="checkbox"&&k.type!=="radio"?_&&mu(_.elementType)&&(te=Gf):te=Xv;if(te&&(te=te(e,_))){Xf(Q,te,n,B);break e}xe&&xe(e,k,_),e==="focusout"&&_&&k.type==="number"&&_.memoizedProps.value!=null&&hu(k,"number",k.value)}switch(xe=_?Jl(_):window,e){case"focusin":(Yf(xe)||xe.contentEditable==="true")&&(hl=xe,Du=_,ir=null);break;case"focusout":ir=Du=hl=null;break;case"mousedown":wu=!0;break;case"contextmenu":case"mouseup":case"dragend":wu=!1,If(Q,n,B);break;case"selectionchange":if($v)break;case"keydown":case"keyup":If(Q,n,B)}var ie;if(Au)e:{switch(e){case"compositionstart":var se="onCompositionStart";break e;case"compositionend":se="onCompositionEnd";break e;case"compositionupdate":se="onCompositionUpdate";break e}se=void 0}else dl?Vf(e,n)&&(se="onCompositionEnd"):e==="keydown"&&n.keyCode===229&&(se="onCompositionStart");se&&(Hf&&n.locale!=="ko"&&(dl||se!=="onCompositionStart"?se==="onCompositionEnd"&&dl&&(ie=Cf()):(Jn=B,yu="value"in Jn?Jn.value:Jn.textContent,dl=!0)),xe=co(_,se),0<xe.length&&(se=new Uf(se,e,null,n,B),Q.push({event:se,listeners:xe}),ie?se.data=ie:(ie=jf(n),ie!==null&&(se.data=ie)))),(ie=qv?Hv(e,n):Lv(e,n))&&(se=co(_,"onBeforeInput"),0<se.length&&(xe=new Uf("onBeforeInput","beforeinput",null,n,B),Q.push({event:xe,listeners:se}),xe.data=ie)),wb(Q,e,_,n,B)}p0(Q,t)})}function Nr(e,t,n){return{instance:e,listener:t,currentTarget:n}}function co(e,t){for(var n=t+"Capture",l=[];e!==null;){var o=e,c=o.stateNode;o=o.tag,o!==5&&o!==26&&o!==27||c===null||(o=Wl(e,n),o!=null&&l.unshift(Nr(e,o,c)),o=Wl(e,t),o!=null&&l.push(Nr(e,o,c))),e=e.return}return l}function Ul(e){if(e===null)return null;do e=e.return;while(e&&e.tag!==5&&e.tag!==27);return e||null}function b0(e,t,n,l,o){for(var c=t._reactName,d=[];n!==null&&n!==l;){var b=n,x=b.alternate,_=b.stateNode;if(b=b.tag,x!==null&&x===l)break;b!==5&&b!==26&&b!==27||_===null||(x=_,o?(_=Wl(n,c),_!=null&&d.unshift(Nr(n,_,x))):o||(_=Wl(n,c),_!=null&&d.push(Nr(n,_,x)))),n=n.return}d.length!==0&&e.push({event:t,listeners:d})}var kb=/\r\n?/g,Nb=/\u0000|\uFFFD/g;function g0(e){return(typeof e=="string"?e:""+e).replace(kb,`
`).replace(Nb,"")}function y0(e,t){return t=g0(t),g0(e)===t}function so(){}function ke(e,t,n,l,o,c){switch(n){case"children":typeof l=="string"?t==="body"||t==="textarea"&&l===""||cl(e,l):(typeof l=="number"||typeof l=="bigint")&&t!=="body"&&cl(e,""+l);break;case"className":gi(e,"class",l);break;case"tabIndex":gi(e,"tabindex",l);break;case"dir":case"role":case"viewBox":case"width":case"height":gi(e,n,l);break;case"style":wf(e,l,c);break;case"data":if(t!=="object"){gi(e,"data",l);break}case"src":case"href":if(l===""&&(t!=="a"||n!=="href")){e.removeAttribute(n);break}if(l==null||typeof l=="function"||typeof l=="symbol"||typeof l=="boolean"){e.removeAttribute(n);break}l=Si(""+l),e.setAttribute(n,l);break;case"action":case"formAction":if(typeof l=="function"){e.setAttribute(n,"javascript:throw new Error('A React form was unexpectedly submitted. If you called form.submit() manually, consider using form.requestSubmit() instead. If you\\'re trying to use event.stopPropagation() in a submit event handler, consider also calling event.preventDefault().')");break}else typeof c=="function"&&(n==="formAction"?(t!=="input"&&ke(e,t,"name",o.name,o,null),ke(e,t,"formEncType",o.formEncType,o,null),ke(e,t,"formMethod",o.formMethod,o,null),ke(e,t,"formTarget",o.formTarget,o,null)):(ke(e,t,"encType",o.encType,o,null),ke(e,t,"method",o.method,o,null),ke(e,t,"target",o.target,o,null)));if(l==null||typeof l=="symbol"||typeof l=="boolean"){e.removeAttribute(n);break}l=Si(""+l),e.setAttribute(n,l);break;case"onClick":l!=null&&(e.onclick=so);break;case"onScroll":l!=null&&_e("scroll",e);break;case"onScrollEnd":l!=null&&_e("scrollend",e);break;case"dangerouslySetInnerHTML":if(l!=null){if(typeof l!="object"||!("__html"in l))throw Error(u(61));if(n=l.__html,n!=null){if(o.children!=null)throw Error(u(60));e.innerHTML=n}}break;case"multiple":e.multiple=l&&typeof l!="function"&&typeof l!="symbol";break;case"muted":e.muted=l&&typeof l!="function"&&typeof l!="symbol";break;case"suppressContentEditableWarning":case"suppressHydrationWarning":case"defaultValue":case"defaultChecked":case"innerHTML":case"ref":break;case"autoFocus":break;case"xlinkHref":if(l==null||typeof l=="function"||typeof l=="boolean"||typeof l=="symbol"){e.removeAttribute("xlink:href");break}n=Si(""+l),e.setAttributeNS("http://www.w3.org/1999/xlink","xlink:href",n);break;case"contentEditable":case"spellCheck":case"draggable":case"value":case"autoReverse":case"externalResourcesRequired":case"focusable":case"preserveAlpha":l!=null&&typeof l!="function"&&typeof l!="symbol"?e.setAttribute(n,""+l):e.removeAttribute(n);break;case"inert":case"allowFullScreen":case"async":case"autoPlay":case"controls":case"default":case"defer":case"disabled":case"disablePictureInPicture":case"disableRemotePlayback":case"formNoValidate":case"hidden":case"loop":case"noModule":case"noValidate":case"open":case"playsInline":case"readOnly":case"required":case"reversed":case"scoped":case"seamless":case"itemScope":l&&typeof l!="function"&&typeof l!="symbol"?e.setAttribute(n,""):e.removeAttribute(n);break;case"capture":case"download":l===!0?e.setAttribute(n,""):l!==!1&&l!=null&&typeof l!="function"&&typeof l!="symbol"?e.setAttribute(n,l):e.removeAttribute(n);break;case"cols":case"rows":case"size":case"span":l!=null&&typeof l!="function"&&typeof l!="symbol"&&!isNaN(l)&&1<=l?e.setAttribute(n,l):e.removeAttribute(n);break;case"rowSpan":case"start":l==null||typeof l=="function"||typeof l=="symbol"||isNaN(l)?e.removeAttribute(n):e.setAttribute(n,l);break;case"popover":_e("beforetoggle",e),_e("toggle",e),bi(e,"popover",l);break;case"xlinkActuate":Tn(e,"http://www.w3.org/1999/xlink","xlink:actuate",l);break;case"xlinkArcrole":Tn(e,"http://www.w3.org/1999/xlink","xlink:arcrole",l);break;case"xlinkRole":Tn(e,"http://www.w3.org/1999/xlink","xlink:role",l);break;case"xlinkShow":Tn(e,"http://www.w3.org/1999/xlink","xlink:show",l);break;case"xlinkTitle":Tn(e,"http://www.w3.org/1999/xlink","xlink:title",l);break;case"xlinkType":Tn(e,"http://www.w3.org/1999/xlink","xlink:type",l);break;case"xmlBase":Tn(e,"http://www.w3.org/XML/1998/namespace","xml:base",l);break;case"xmlLang":Tn(e,"http://www.w3.org/XML/1998/namespace","xml:lang",l);break;case"xmlSpace":Tn(e,"http://www.w3.org/XML/1998/namespace","xml:space",l);break;case"is":bi(e,"is",l);break;case"innerText":case"textContent":break;default:(!(2<n.length)||n[0]!=="o"&&n[0]!=="O"||n[1]!=="n"&&n[1]!=="N")&&(n=cv.get(n)||n,bi(e,n,l))}}function Wc(e,t,n,l,o,c){switch(n){case"style":wf(e,l,c);break;case"dangerouslySetInnerHTML":if(l!=null){if(typeof l!="object"||!("__html"in l))throw Error(u(61));if(n=l.__html,n!=null){if(o.children!=null)throw Error(u(60));e.innerHTML=n}}break;case"children":typeof l=="string"?cl(e,l):(typeof l=="number"||typeof l=="bigint")&&cl(e,""+l);break;case"onScroll":l!=null&&_e("scroll",e);break;case"onScrollEnd":l!=null&&_e("scrollend",e);break;case"onClick":l!=null&&(e.onclick=so);break;case"suppressContentEditableWarning":case"suppressHydrationWarning":case"innerHTML":case"ref":break;case"innerText":case"textContent":break;default:if(!xf.hasOwnProperty(n))e:{if(n[0]==="o"&&n[1]==="n"&&(o=n.endsWith("Capture"),t=n.slice(2,o?n.length-7:void 0),c=e[wt]||null,c=c!=null?c[n]:null,typeof c=="function"&&e.removeEventListener(t,c,o),typeof l=="function")){typeof c!="function"&&c!==null&&(n in e?e[n]=null:e.hasAttribute(n)&&e.removeAttribute(n)),e.addEventListener(t,l,o);break e}n in e?e[n]=l:l===!0?e.setAttribute(n,""):bi(e,n,l)}}}function bt(e,t,n){switch(t){case"div":case"span":case"svg":case"path":case"a":case"g":case"p":case"li":break;case"img":_e("error",e),_e("load",e);var l=!1,o=!1,c;for(c in n)if(n.hasOwnProperty(c)){var d=n[c];if(d!=null)switch(c){case"src":l=!0;break;case"srcSet":o=!0;break;case"children":case"dangerouslySetInnerHTML":throw Error(u(137,t));default:ke(e,t,c,d,n,null)}}o&&ke(e,t,"srcSet",n.srcSet,n,null),l&&ke(e,t,"src",n.src,n,null);return;case"input":_e("invalid",e);var b=c=d=o=null,x=null,_=null;for(l in n)if(n.hasOwnProperty(l)){var B=n[l];if(B!=null)switch(l){case"name":o=B;break;case"type":d=B;break;case"checked":x=B;break;case"defaultChecked":_=B;break;case"value":c=B;break;case"defaultValue":b=B;break;case"children":case"dangerouslySetInnerHTML":if(B!=null)throw Error(u(137,t));break;default:ke(e,t,l,B,n,null)}}Tf(e,c,b,x,_,d,o,!1),yi(e);return;case"select":_e("invalid",e),l=d=c=null;for(o in n)if(n.hasOwnProperty(o)&&(b=n[o],b!=null))switch(o){case"value":c=b;break;case"defaultValue":d=b;break;case"multiple":l=b;default:ke(e,t,o,b,n,null)}t=c,n=d,e.multiple=!!l,t!=null?ul(e,!!l,t,!1):n!=null&&ul(e,!!l,n,!0);return;case"textarea":_e("invalid",e),c=o=l=null;for(d in n)if(n.hasOwnProperty(d)&&(b=n[d],b!=null))switch(d){case"value":l=b;break;case"defaultValue":o=b;break;case"children":c=b;break;case"dangerouslySetInnerHTML":if(b!=null)throw Error(u(91));break;default:ke(e,t,d,b,n,null)}zf(e,l,o,c),yi(e);return;case"option":for(x in n)if(n.hasOwnProperty(x)&&(l=n[x],l!=null))switch(x){case"selected":e.selected=l&&typeof l!="function"&&typeof l!="symbol";break;default:ke(e,t,x,l,n,null)}return;case"dialog":_e("cancel",e),_e("close",e);break;case"iframe":case"object":_e("load",e);break;case"video":case"audio":for(l=0;l<kr.length;l++)_e(kr[l],e);break;case"image":_e("error",e),_e("load",e);break;case"details":_e("toggle",e);break;case"embed":case"source":case"link":_e("error",e),_e("load",e);case"area":case"base":case"br":case"col":case"hr":case"keygen":case"meta":case"param":case"track":case"wbr":case"menuitem":for(_ in n)if(n.hasOwnProperty(_)&&(l=n[_],l!=null))switch(_){case"children":case"dangerouslySetInnerHTML":throw Error(u(137,t));default:ke(e,t,_,l,n,null)}return;default:if(mu(t)){for(B in n)n.hasOwnProperty(B)&&(l=n[B],l!==void 0&&Wc(e,t,B,l,n,void 0));return}}for(b in n)n.hasOwnProperty(b)&&(l=n[b],l!=null&&ke(e,t,b,l,n,null))}function Ub(e,t,n,l){switch(t){case"div":case"span":case"svg":case"path":case"a":case"g":case"p":case"li":break;case"input":var o=null,c=null,d=null,b=null,x=null,_=null,B=null;for(L in n){var Q=n[L];if(n.hasOwnProperty(L)&&Q!=null)switch(L){case"checked":break;case"value":break;case"defaultValue":x=Q;default:l.hasOwnProperty(L)||ke(e,t,L,null,l,Q)}}for(var k in l){var L=l[k];if(Q=n[k],l.hasOwnProperty(k)&&(L!=null||Q!=null))switch(k){case"type":c=L;break;case"name":o=L;break;case"checked":_=L;break;case"defaultChecked":B=L;break;case"value":d=L;break;case"defaultValue":b=L;break;case"children":case"dangerouslySetInnerHTML":if(L!=null)throw Error(u(137,t));break;default:L!==Q&&ke(e,t,k,L,l,Q)}}du(e,d,b,x,_,B,c,o);return;case"select":L=d=b=k=null;for(c in n)if(x=n[c],n.hasOwnProperty(c)&&x!=null)switch(c){case"value":break;case"multiple":L=x;default:l.hasOwnProperty(c)||ke(e,t,c,null,l,x)}for(o in l)if(c=l[o],x=n[o],l.hasOwnProperty(o)&&(c!=null||x!=null))switch(o){case"value":k=c;break;case"defaultValue":b=c;break;case"multiple":d=c;default:c!==x&&ke(e,t,o,c,l,x)}t=b,n=d,l=L,k!=null?ul(e,!!n,k,!1):!!l!=!!n&&(t!=null?ul(e,!!n,t,!0):ul(e,!!n,n?[]:"",!1));return;case"textarea":L=k=null;for(b in n)if(o=n[b],n.hasOwnProperty(b)&&o!=null&&!l.hasOwnProperty(b))switch(b){case"value":break;case"children":break;default:ke(e,t,b,null,l,o)}for(d in l)if(o=l[d],c=n[d],l.hasOwnProperty(d)&&(o!=null||c!=null))switch(d){case"value":k=o;break;case"defaultValue":L=o;break;case"children":break;case"dangerouslySetInnerHTML":if(o!=null)throw Error(u(91));break;default:o!==c&&ke(e,t,d,o,l,c)}_f(e,k,L);return;case"option":for(var le in n)if(k=n[le],n.hasOwnProperty(le)&&k!=null&&!l.hasOwnProperty(le))switch(le){case"selected":e.selected=!1;break;default:ke(e,t,le,null,l,k)}for(x in l)if(k=l[x],L=n[x],l.hasOwnProperty(x)&&k!==L&&(k!=null||L!=null))switch(x){case"selected":e.selected=k&&typeof k!="function"&&typeof k!="symbol";break;default:ke(e,t,x,k,l,L)}return;case"img":case"link":case"area":case"base":case"br":case"col":case"embed":case"hr":case"keygen":case"meta":case"param":case"source":case"track":case"wbr":case"menuitem":for(var fe in n)k=n[fe],n.hasOwnProperty(fe)&&k!=null&&!l.hasOwnProperty(fe)&&ke(e,t,fe,null,l,k);for(_ in l)if(k=l[_],L=n[_],l.hasOwnProperty(_)&&k!==L&&(k!=null||L!=null))switch(_){case"children":case"dangerouslySetInnerHTML":if(k!=null)throw Error(u(137,t));break;default:ke(e,t,_,k,l,L)}return;default:if(mu(t)){for(var Ke in n)k=n[Ke],n.hasOwnProperty(Ke)&&k!==void 0&&!l.hasOwnProperty(Ke)&&Wc(e,t,Ke,void 0,l,k);for(B in l)k=l[B],L=n[B],!l.hasOwnProperty(B)||k===L||k===void 0&&L===void 0||Wc(e,t,B,k,l,L);return}}for(var D in n)k=n[D],n.hasOwnProperty(D)&&k!=null&&!l.hasOwnProperty(D)&&ke(e,t,D,null,l,k);for(Q in l)k=l[Q],L=n[Q],!l.hasOwnProperty(Q)||k===L||k==null&&L==null||ke(e,t,Q,k,l,L)}var Ic=null,es=null;function fo(e){return e.nodeType===9?e:e.ownerDocument}function x0(e){switch(e){case"http://www.w3.org/2000/svg":return 1;case"http://www.w3.org/1998/Math/MathML":return 2;default:return 0}}function S0(e,t){if(e===0)switch(t){case"svg":return 1;case"math":return 2;default:return 0}return e===1&&t==="foreignObject"?0:e}function ts(e,t){return e==="textarea"||e==="noscript"||typeof t.children=="string"||typeof t.children=="number"||typeof t.children=="bigint"||typeof t.dangerouslySetInnerHTML=="object"&&t.dangerouslySetInnerHTML!==null&&t.dangerouslySetInnerHTML.__html!=null}var ns=null;function qb(){var e=window.event;return e&&e.type==="popstate"?e===ns?!1:(ns=e,!0):(ns=null,!1)}var E0=typeof setTimeout=="function"?setTimeout:void 0,Hb=typeof clearTimeout=="function"?clearTimeout:void 0,O0=typeof Promise=="function"?Promise:void 0,Lb=typeof queueMicrotask=="function"?queueMicrotask:typeof O0<"u"?function(e){return O0.resolve(null).then(e).catch(Bb)}:E0;function Bb(e){setTimeout(function(){throw e})}function as(e,t){var n=t,l=0;do{var o=n.nextSibling;if(e.removeChild(n),o&&o.nodeType===8)if(n=o.data,n==="/$"){if(l===0){e.removeChild(o),Yr(t);return}l--}else n!=="$"&&n!=="$?"&&n!=="$!"||l++;n=o}while(n);Yr(t)}function ls(e){var t=e.firstChild;for(t&&t.nodeType===10&&(t=t.nextSibling);t;){var n=t;switch(t=t.nextSibling,n.nodeName){case"HTML":case"HEAD":case"BODY":ls(n),fu(n);continue;case"SCRIPT":case"STYLE":continue;case"LINK":if(n.rel.toLowerCase()==="stylesheet")continue}e.removeChild(n)}}function Vb(e,t,n,l){for(;e.nodeType===1;){var o=n;if(e.nodeName.toLowerCase()!==t.toLowerCase()){if(!l&&(e.nodeName!=="INPUT"||e.type!=="hidden"))break}else if(l){if(!e[Kl])switch(t){case"meta":if(!e.hasAttribute("itemprop"))break;return e;case"link":if(c=e.getAttribute("rel"),c==="stylesheet"&&e.hasAttribute("data-precedence"))break;if(c!==o.rel||e.getAttribute("href")!==(o.href==null?null:o.href)||e.getAttribute("crossorigin")!==(o.crossOrigin==null?null:o.crossOrigin)||e.getAttribute("title")!==(o.title==null?null:o.title))break;return e;case"style":if(e.hasAttribute("data-precedence"))break;return e;case"script":if(c=e.getAttribute("src"),(c!==(o.src==null?null:o.src)||e.getAttribute("type")!==(o.type==null?null:o.type)||e.getAttribute("crossorigin")!==(o.crossOrigin==null?null:o.crossOrigin))&&c&&e.hasAttribute("async")&&!e.hasAttribute("itemprop"))break;return e;default:return e}}else if(t==="input"&&e.type==="hidden"){var c=o.name==null?null:""+o.name;if(o.type==="hidden"&&e.getAttribute("name")===c)return e}else return e;if(e=rn(e.nextSibling),e===null)break}return null}function jb(e,t,n){if(t==="")return null;for(;e.nodeType!==3;)if((e.nodeType!==1||e.nodeName!=="INPUT"||e.type!=="hidden")&&!n||(e=rn(e.nextSibling),e===null))return null;return e}function rn(e){for(;e!=null;e=e.nextSibling){var t=e.nodeType;if(t===1||t===3)break;if(t===8){if(t=e.data,t==="$"||t==="$!"||t==="$?"||t==="F!"||t==="F")break;if(t==="/$")return null}}return e}function A0(e){e=e.previousSibling;for(var t=0;e;){if(e.nodeType===8){var n=e.data;if(n==="$"||n==="$!"||n==="$?"){if(t===0)return e;t--}else n==="/$"&&t++}e=e.previousSibling}return null}function T0(e,t,n){switch(t=fo(n),e){case"html":if(e=t.documentElement,!e)throw Error(u(452));return e;case"head":if(e=t.head,!e)throw Error(u(453));return e;case"body":if(e=t.body,!e)throw Error(u(454));return e;default:throw Error(u(451))}}var Jt=new Map,_0=new Set;function ho(e){return typeof e.getRootNode=="function"?e.getRootNode():e.ownerDocument}var Vn=ne.d;ne.d={f:Yb,r:Xb,D:Gb,C:Qb,L:Zb,m:$b,X:Fb,S:Pb,M:Kb};function Yb(){var e=Vn.f(),t=lo();return e||t}function Xb(e){var t=rl(e);t!==null&&t.tag===5&&t.type==="form"?Id(t):Vn.r(e)}var ql=typeof document>"u"?null:document;function z0(e,t,n){var l=ql;if(l&&typeof t=="string"&&t){var o=jt(t);o='link[rel="'+e+'"][href="'+o+'"]',typeof n=="string"&&(o+='[crossorigin="'+n+'"]'),_0.has(o)||(_0.add(o),e={rel:e,crossOrigin:n,href:t},l.querySelector(o)===null&&(t=l.createElement("link"),bt(t,"link",e),st(t),l.head.appendChild(t)))}}function Gb(e){Vn.D(e),z0("dns-prefetch",e,null)}function Qb(e,t){Vn.C(e,t),z0("preconnect",e,t)}function Zb(e,t,n){Vn.L(e,t,n);var l=ql;if(l&&e&&t){var o='link[rel="preload"][as="'+jt(t)+'"]';t==="image"&&n&&n.imageSrcSet?(o+='[imagesrcset="'+jt(n.imageSrcSet)+'"]',typeof n.imageSizes=="string"&&(o+='[imagesizes="'+jt(n.imageSizes)+'"]')):o+='[href="'+jt(e)+'"]';var c=o;switch(t){case"style":c=Hl(e);break;case"script":c=Ll(e)}Jt.has(c)||(e=W({rel:"preload",href:t==="image"&&n&&n.imageSrcSet?void 0:e,as:t},n),Jt.set(c,e),l.querySelector(o)!==null||t==="style"&&l.querySelector(Ur(c))||t==="script"&&l.querySelector(qr(c))||(t=l.createElement("link"),bt(t,"link",e),st(t),l.head.appendChild(t)))}}function $b(e,t){Vn.m(e,t);var n=ql;if(n&&e){var l=t&&typeof t.as=="string"?t.as:"script",o='link[rel="modulepreload"][as="'+jt(l)+'"][href="'+jt(e)+'"]',c=o;switch(l){case"audioworklet":case"paintworklet":case"serviceworker":case"sharedworker":case"worker":case"script":c=Ll(e)}if(!Jt.has(c)&&(e=W({rel:"modulepreload",href:e},t),Jt.set(c,e),n.querySelector(o)===null)){switch(l){case"audioworklet":case"paintworklet":case"serviceworker":case"sharedworker":case"worker":case"script":if(n.querySelector(qr(c)))return}l=n.createElement("link"),bt(l,"link",e),st(l),n.head.appendChild(l)}}}function Pb(e,t,n){Vn.S(e,t,n);var l=ql;if(l&&e){var o=il(l).hoistableStyles,c=Hl(e);t=t||"default";var d=o.get(c);if(!d){var b={loading:0,preload:null};if(d=l.querySelector(Ur(c)))b.loading=5;else{e=W({rel:"stylesheet",href:e,"data-precedence":t},n),(n=Jt.get(c))&&rs(e,n);var x=d=l.createElement("link");st(x),bt(x,"link",e),x._p=new Promise(function(_,B){x.onload=_,x.onerror=B}),x.addEventListener("load",function(){b.loading|=1}),x.addEventListener("error",function(){b.loading|=2}),b.loading|=4,mo(d,t,l)}d={type:"stylesheet",instance:d,count:1,state:b},o.set(c,d)}}}function Fb(e,t){Vn.X(e,t);var n=ql;if(n&&e){var l=il(n).hoistableScripts,o=Ll(e),c=l.get(o);c||(c=n.querySelector(qr(o)),c||(e=W({src:e,async:!0},t),(t=Jt.get(o))&&is(e,t),c=n.createElement("script"),st(c),bt(c,"link",e),n.head.appendChild(c)),c={type:"script",instance:c,count:1,state:null},l.set(o,c))}}function Kb(e,t){Vn.M(e,t);var n=ql;if(n&&e){var l=il(n).hoistableScripts,o=Ll(e),c=l.get(o);c||(c=n.querySelector(qr(o)),c||(e=W({src:e,async:!0,type:"module"},t),(t=Jt.get(o))&&is(e,t),c=n.createElement("script"),st(c),bt(c,"link",e),n.head.appendChild(c)),c={type:"script",instance:c,count:1,state:null},l.set(o,c))}}function D0(e,t,n,l){var o=(o=hn.current)?ho(o):null;if(!o)throw Error(u(446));switch(e){case"meta":case"title":return null;case"style":return typeof n.precedence=="string"&&typeof n.href=="string"?(t=Hl(n.href),n=il(o).hoistableStyles,l=n.get(t),l||(l={type:"style",instance:null,count:0,state:null},n.set(t,l)),l):{type:"void",instance:null,count:0,state:null};case"link":if(n.rel==="stylesheet"&&typeof n.href=="string"&&typeof n.precedence=="string"){e=Hl(n.href);var c=il(o).hoistableStyles,d=c.get(e);if(d||(o=o.ownerDocument||o,d={type:"stylesheet",instance:null,count:0,state:{loading:0,preload:null}},c.set(e,d),(c=o.querySelector(Ur(e)))&&!c._p&&(d.instance=c,d.state.loading=5),Jt.has(e)||(n={rel:"preload",as:"style",href:n.href,crossOrigin:n.crossOrigin,integrity:n.integrity,media:n.media,hrefLang:n.hrefLang,referrerPolicy:n.referrerPolicy},Jt.set(e,n),c||Jb(o,e,n,d.state))),t&&l===null)throw Error(u(528,""));return d}if(t&&l!==null)throw Error(u(529,""));return null;case"script":return t=n.async,n=n.src,typeof n=="string"&&t&&typeof t!="function"&&typeof t!="symbol"?(t=Ll(n),n=il(o).hoistableScripts,l=n.get(t),l||(l={type:"script",instance:null,count:0,state:null},n.set(t,l)),l):{type:"void",instance:null,count:0,state:null};default:throw Error(u(444,e))}}function Hl(e){return'href="'+jt(e)+'"'}function Ur(e){return'link[rel="stylesheet"]['+e+"]"}function w0(e){return W({},e,{"data-precedence":e.precedence,precedence:null})}function Jb(e,t,n,l){e.querySelector('link[rel="preload"][as="style"]['+t+"]")?l.loading=1:(t=e.createElement("link"),l.preload=t,t.addEventListener("load",function(){return l.loading|=1}),t.addEventListener("error",function(){return l.loading|=2}),bt(t,"link",n),st(t),e.head.appendChild(t))}function Ll(e){return'[src="'+jt(e)+'"]'}function qr(e){return"script[async]"+e}function R0(e,t,n){if(t.count++,t.instance===null)switch(t.type){case"style":var l=e.querySelector('style[data-href~="'+jt(n.href)+'"]');if(l)return t.instance=l,st(l),l;var o=W({},n,{"data-href":n.href,"data-precedence":n.precedence,href:null,precedence:null});return l=(e.ownerDocument||e).createElement("style"),st(l),bt(l,"style",o),mo(l,n.precedence,e),t.instance=l;case"stylesheet":o=Hl(n.href);var c=e.querySelector(Ur(o));if(c)return t.state.loading|=4,t.instance=c,st(c),c;l=w0(n),(o=Jt.get(o))&&rs(l,o),c=(e.ownerDocument||e).createElement("link"),st(c);var d=c;return d._p=new Promise(function(b,x){d.onload=b,d.onerror=x}),bt(c,"link",l),t.state.loading|=4,mo(c,n.precedence,e),t.instance=c;case"script":return c=Ll(n.src),(o=e.querySelector(qr(c)))?(t.instance=o,st(o),o):(l=n,(o=Jt.get(c))&&(l=W({},n),is(l,o)),e=e.ownerDocument||e,o=e.createElement("script"),st(o),bt(o,"link",l),e.head.appendChild(o),t.instance=o);case"void":return null;default:throw Error(u(443,t.type))}else t.type==="stylesheet"&&(t.state.loading&4)===0&&(l=t.instance,t.state.loading|=4,mo(l,n.precedence,e));return t.instance}function mo(e,t,n){for(var l=n.querySelectorAll('link[rel="stylesheet"][data-precedence],style[data-precedence]'),o=l.length?l[l.length-1]:null,c=o,d=0;d<l.length;d++){var b=l[d];if(b.dataset.precedence===t)c=b;else if(c!==o)break}c?c.parentNode.insertBefore(e,c.nextSibling):(t=n.nodeType===9?n.head:n,t.insertBefore(e,t.firstChild))}function rs(e,t){e.crossOrigin==null&&(e.crossOrigin=t.crossOrigin),e.referrerPolicy==null&&(e.referrerPolicy=t.referrerPolicy),e.title==null&&(e.title=t.title)}function is(e,t){e.crossOrigin==null&&(e.crossOrigin=t.crossOrigin),e.referrerPolicy==null&&(e.referrerPolicy=t.referrerPolicy),e.integrity==null&&(e.integrity=t.integrity)}var po=null;function M0(e,t,n){if(po===null){var l=new Map,o=po=new Map;o.set(n,l)}else o=po,l=o.get(n),l||(l=new Map,o.set(n,l));if(l.has(e))return l;for(l.set(e,null),n=n.getElementsByTagName(e),o=0;o<n.length;o++){var c=n[o];if(!(c[Kl]||c[yt]||e==="link"&&c.getAttribute("rel")==="stylesheet")&&c.namespaceURI!=="http://www.w3.org/2000/svg"){var d=c.getAttribute(t)||"";d=e+d;var b=l.get(d);b?b.push(c):l.set(d,[c])}}return l}function C0(e,t,n){e=e.ownerDocument||e,e.head.insertBefore(n,t==="title"?e.querySelector("head > title"):null)}function Wb(e,t,n){if(n===1||t.itemProp!=null)return!1;switch(e){case"meta":case"title":return!0;case"style":if(typeof t.precedence!="string"||typeof t.href!="string"||t.href==="")break;return!0;case"link":if(typeof t.rel!="string"||typeof t.href!="string"||t.href===""||t.onLoad||t.onError)break;switch(t.rel){case"stylesheet":return e=t.disabled,typeof t.precedence=="string"&&e==null;default:return!0}case"script":if(t.async&&typeof t.async!="function"&&typeof t.async!="symbol"&&!t.onLoad&&!t.onError&&t.src&&typeof t.src=="string")return!0}return!1}function k0(e){return!(e.type==="stylesheet"&&(e.state.loading&3)===0)}var Hr=null;function Ib(){}function eg(e,t,n){if(Hr===null)throw Error(u(475));var l=Hr;if(t.type==="stylesheet"&&(typeof n.media!="string"||matchMedia(n.media).matches!==!1)&&(t.state.loading&4)===0){if(t.instance===null){var o=Hl(n.href),c=e.querySelector(Ur(o));if(c){e=c._p,e!==null&&typeof e=="object"&&typeof e.then=="function"&&(l.count++,l=vo.bind(l),e.then(l,l)),t.state.loading|=4,t.instance=c,st(c);return}c=e.ownerDocument||e,n=w0(n),(o=Jt.get(o))&&rs(n,o),c=c.createElement("link"),st(c);var d=c;d._p=new Promise(function(b,x){d.onload=b,d.onerror=x}),bt(c,"link",n),t.instance=c}l.stylesheets===null&&(l.stylesheets=new Map),l.stylesheets.set(t,e),(e=t.state.preload)&&(t.state.loading&3)===0&&(l.count++,t=vo.bind(l),e.addEventListener("load",t),e.addEventListener("error",t))}}function tg(){if(Hr===null)throw Error(u(475));var e=Hr;return e.stylesheets&&e.count===0&&os(e,e.stylesheets),0<e.count?function(t){var n=setTimeout(function(){if(e.stylesheets&&os(e,e.stylesheets),e.unsuspend){var l=e.unsuspend;e.unsuspend=null,l()}},6e4);return e.unsuspend=t,function(){e.unsuspend=null,clearTimeout(n)}}:null}function vo(){if(this.count--,this.count===0){if(this.stylesheets)os(this,this.stylesheets);else if(this.unsuspend){var e=this.unsuspend;this.unsuspend=null,e()}}}var bo=null;function os(e,t){e.stylesheets=null,e.unsuspend!==null&&(e.count++,bo=new Map,t.forEach(ng,e),bo=null,vo.call(e))}function ng(e,t){if(!(t.state.loading&4)){var n=bo.get(e);if(n)var l=n.get(null);else{n=new Map,bo.set(e,n);for(var o=e.querySelectorAll("link[data-precedence],style[data-precedence]"),c=0;c<o.length;c++){var d=o[c];(d.nodeName==="LINK"||d.getAttribute("media")!=="not all")&&(n.set(d.dataset.precedence,d),l=d)}l&&n.set(null,l)}o=t.instance,d=o.getAttribute("data-precedence"),c=n.get(d)||l,c===l&&n.set(null,o),n.set(d,o),this.count++,l=vo.bind(this),o.addEventListener("load",l),o.addEventListener("error",l),c?c.parentNode.insertBefore(o,c.nextSibling):(e=e.nodeType===9?e.head:e,e.insertBefore(o,e.firstChild)),t.state.loading|=4}}var Lr={$$typeof:w,Provider:null,Consumer:null,_currentValue:ye,_currentValue2:ye,_threadCount:0};function ag(e,t,n,l,o,c,d,b){this.tag=1,this.containerInfo=e,this.finishedWork=this.pingCache=this.current=this.pendingChildren=null,this.timeoutHandle=-1,this.callbackNode=this.next=this.pendingContext=this.context=this.cancelPendingCommit=null,this.callbackPriority=0,this.expirationTimes=cu(-1),this.entangledLanes=this.shellSuspendCounter=this.errorRecoveryDisabledLanes=this.finishedLanes=this.expiredLanes=this.warmLanes=this.pingedLanes=this.suspendedLanes=this.pendingLanes=0,this.entanglements=cu(0),this.hiddenUpdates=cu(null),this.identifierPrefix=l,this.onUncaughtError=o,this.onCaughtError=c,this.onRecoverableError=d,this.pooledCache=null,this.pooledCacheLanes=0,this.formState=b,this.incompleteTransitions=new Map}function N0(e,t,n,l,o,c,d,b,x,_,B,Q){return e=new ag(e,t,n,d,b,x,_,Q),t=1,c===!0&&(t|=24),c=Ft(3,null,null,t),e.current=c,c.stateNode=e,t=Bu(),t.refCount++,e.pooledCache=t,t.refCount++,c.memoizedState={element:l,isDehydrated:n,cache:t},yc(c),e}function U0(e){return e?(e=vl,e):vl}function q0(e,t,n,l,o,c){o=U0(o),l.context===null?l.context=o:l.pendingContext=o,l=ra(t),l.payload={element:n},c=c===void 0?null:c,c!==null&&(l.callback=c),n=ia(e,l,t),n!==null&&(At(n,e,t),Sr(n,e,t))}function H0(e,t){if(e=e.memoizedState,e!==null&&e.dehydrated!==null){var n=e.retryLane;e.retryLane=n!==0&&n<t?n:t}}function us(e,t){H0(e,t),(e=e.alternate)&&H0(e,t)}function L0(e){if(e.tag===13){var t=Wn(e,67108864);t!==null&&At(t,e,67108864),us(e,67108864)}}var go=!0;function lg(e,t,n,l){var o=G.T;G.T=null;var c=ne.p;try{ne.p=2,cs(e,t,n,l)}finally{ne.p=c,G.T=o}}function rg(e,t,n,l){var o=G.T;G.T=null;var c=ne.p;try{ne.p=8,cs(e,t,n,l)}finally{ne.p=c,G.T=o}}function cs(e,t,n,l){if(go){var o=ss(l);if(o===null)Jc(e,t,l,yo,n),V0(e,l);else if(og(o,e,t,n,l))l.stopPropagation();else if(V0(e,l),t&4&&-1<ig.indexOf(e)){for(;o!==null;){var c=rl(o);if(c!==null)switch(c.tag){case 3:if(c=c.stateNode,c.current.memoizedState.isDehydrated){var d=_a(c.pendingLanes);if(d!==0){var b=c;for(b.pendingLanes|=2,b.entangledLanes|=2;d;){var x=1<<31-Et(d);b.entanglements[1]|=x,d&=~x}gn(c),(Qe&6)===0&&(to=H()+500,Cr(0))}}break;case 13:b=Wn(c,2),b!==null&&At(b,c,2),lo(),us(c,2)}if(c=ss(l),c===null&&Jc(e,t,l,yo,n),c===o)break;o=c}o!==null&&l.stopPropagation()}else Jc(e,t,l,null,n)}}function ss(e){return e=vu(e),fs(e)}var yo=null;function fs(e){if(yo=null,e=za(e),e!==null){var t=Ve(e);if(t===null)e=null;else{var n=t.tag;if(n===13){if(e=Je(t),e!==null)return e;e=null}else if(n===3){if(t.stateNode.current.memoizedState.isDehydrated)return t.tag===3?t.stateNode.containerInfo:null;e=null}else t!==e&&(e=null)}}return yo=e,null}function B0(e){switch(e){case"beforetoggle":case"cancel":case"click":case"close":case"contextmenu":case"copy":case"cut":case"auxclick":case"dblclick":case"dragend":case"dragstart":case"drop":case"focusin":case"focusout":case"input":case"invalid":case"keydown":case"keypress":case"keyup":case"mousedown":case"mouseup":case"paste":case"pause":case"play":case"pointercancel":case"pointerdown":case"pointerup":case"ratechange":case"reset":case"resize":case"seeked":case"submit":case"toggle":case"touchcancel":case"touchend":case"touchstart":case"volumechange":case"change":case"selectionchange":case"textInput":case"compositionstart":case"compositionend":case"compositionupdate":case"beforeblur":case"afterblur":case"beforeinput":case"blur":case"fullscreenchange":case"focus":case"hashchange":case"popstate":case"select":case"selectstart":return 2;case"drag":case"dragenter":case"dragexit":case"dragleave":case"dragover":case"mousemove":case"mouseout":case"mouseover":case"pointermove":case"pointerout":case"pointerover":case"scroll":case"touchmove":case"wheel":case"mouseenter":case"mouseleave":case"pointerenter":case"pointerleave":return 8;case"message":switch(J()){case P:return 2;case Z:return 8;case I:case ze:return 32;case Xe:return 268435456;default:return 32}default:return 32}}var ds=!1,ha=null,ma=null,pa=null,Br=new Map,Vr=new Map,va=[],ig="mousedown mouseup touchcancel touchend touchstart auxclick dblclick pointercancel pointerdown pointerup dragend dragstart drop compositionend compositionstart keydown keypress keyup input textInput copy cut paste click change contextmenu reset".split(" ");function V0(e,t){switch(e){case"focusin":case"focusout":ha=null;break;case"dragenter":case"dragleave":ma=null;break;case"mouseover":case"mouseout":pa=null;break;case"pointerover":case"pointerout":Br.delete(t.pointerId);break;case"gotpointercapture":case"lostpointercapture":Vr.delete(t.pointerId)}}function jr(e,t,n,l,o,c){return e===null||e.nativeEvent!==c?(e={blockedOn:t,domEventName:n,eventSystemFlags:l,nativeEvent:c,targetContainers:[o]},t!==null&&(t=rl(t),t!==null&&L0(t)),e):(e.eventSystemFlags|=l,t=e.targetContainers,o!==null&&t.indexOf(o)===-1&&t.push(o),e)}function og(e,t,n,l,o){switch(t){case"focusin":return ha=jr(ha,e,t,n,l,o),!0;case"dragenter":return ma=jr(ma,e,t,n,l,o),!0;case"mouseover":return pa=jr(pa,e,t,n,l,o),!0;case"pointerover":var c=o.pointerId;return Br.set(c,jr(Br.get(c)||null,e,t,n,l,o)),!0;case"gotpointercapture":return c=o.pointerId,Vr.set(c,jr(Vr.get(c)||null,e,t,n,l,o)),!0}return!1}function j0(e){var t=za(e.target);if(t!==null){var n=Ve(t);if(n!==null){if(t=n.tag,t===13){if(t=Je(n),t!==null){e.blockedOn=t,tv(e.priority,function(){if(n.tag===13){var l=Ht(),o=Wn(n,l);o!==null&&At(o,n,l),us(n,l)}});return}}else if(t===3&&n.stateNode.current.memoizedState.isDehydrated){e.blockedOn=n.tag===3?n.stateNode.containerInfo:null;return}}}e.blockedOn=null}function xo(e){if(e.blockedOn!==null)return!1;for(var t=e.targetContainers;0<t.length;){var n=ss(e.nativeEvent);if(n===null){n=e.nativeEvent;var l=new n.constructor(n.type,n);pu=l,n.target.dispatchEvent(l),pu=null}else return t=rl(n),t!==null&&L0(t),e.blockedOn=n,!1;t.shift()}return!0}function Y0(e,t,n){xo(e)&&n.delete(t)}function ug(){ds=!1,ha!==null&&xo(ha)&&(ha=null),ma!==null&&xo(ma)&&(ma=null),pa!==null&&xo(pa)&&(pa=null),Br.forEach(Y0),Vr.forEach(Y0)}function So(e,t){e.blockedOn===t&&(e.blockedOn=null,ds||(ds=!0,a.unstable_scheduleCallback(a.unstable_NormalPriority,ug)))}var Eo=null;function X0(e){Eo!==e&&(Eo=e,a.unstable_scheduleCallback(a.unstable_NormalPriority,function(){Eo===e&&(Eo=null);for(var t=0;t<e.length;t+=3){var n=e[t],l=e[t+1],o=e[t+2];if(typeof l!="function"){if(fs(l||n)===null)continue;break}var c=rl(n);c!==null&&(e.splice(t,3),t-=3,tc(c,{pending:!0,data:o,method:n.method,action:l},l,o))}}))}function Yr(e){function t(x){return So(x,e)}ha!==null&&So(ha,e),ma!==null&&So(ma,e),pa!==null&&So(pa,e),Br.forEach(t),Vr.forEach(t);for(var n=0;n<va.length;n++){var l=va[n];l.blockedOn===e&&(l.blockedOn=null)}for(;0<va.length&&(n=va[0],n.blockedOn===null);)j0(n),n.blockedOn===null&&va.shift();if(n=(e.ownerDocument||e).$$reactFormReplay,n!=null)for(l=0;l<n.length;l+=3){var o=n[l],c=n[l+1],d=o[wt]||null;if(typeof c=="function")d||X0(n);else if(d){var b=null;if(c&&c.hasAttribute("formAction")){if(o=c,d=c[wt]||null)b=d.formAction;else if(fs(o)!==null)continue}else b=d.action;typeof b=="function"?n[l+1]=b:(n.splice(l,3),l-=3),X0(n)}}}function hs(e){this._internalRoot=e}Oo.prototype.render=hs.prototype.render=function(e){var t=this._internalRoot;if(t===null)throw Error(u(409));var n=t.current,l=Ht();q0(n,l,e,t,null,null)},Oo.prototype.unmount=hs.prototype.unmount=function(){var e=this._internalRoot;if(e!==null){this._internalRoot=null;var t=e.containerInfo;e.tag===0&&kl(),q0(e.current,2,null,e,null,null),lo(),t[ll]=null}};function Oo(e){this._internalRoot=e}Oo.prototype.unstable_scheduleHydration=function(e){if(e){var t=bf();e={blockedOn:null,target:e,priority:t};for(var n=0;n<va.length&&t!==0&&t<va[n].priority;n++);va.splice(n,0,e),n===0&&j0(e)}};var G0=r.version;if(G0!=="19.0.0")throw Error(u(527,G0,"19.0.0"));ne.findDOMNode=function(e){var t=e._reactInternals;if(t===void 0)throw typeof e.render=="function"?Error(u(188)):(e=Object.keys(e).join(","),Error(u(268,e)));return e=Y(t),e=e!==null?ue(e):null,e=e===null?null:e.stateNode,e};var cg={bundleType:0,version:"19.0.0",rendererPackageName:"react-dom",currentDispatcherRef:G,findFiberByHostInstance:za,reconcilerVersion:"19.0.0"};if(typeof __REACT_DEVTOOLS_GLOBAL_HOOK__<"u"){var Ao=__REACT_DEVTOOLS_GLOBAL_HOOK__;if(!Ao.isDisabled&&Ao.supportsFiber)try{On=Ao.inject(cg),mt=Ao}catch{}}return Gr.createRoot=function(e,t){if(!s(e))throw Error(u(299));var n=!1,l="",o=oh,c=uh,d=ch,b=null;return t!=null&&(t.unstable_strictMode===!0&&(n=!0),t.identifierPrefix!==void 0&&(l=t.identifierPrefix),t.onUncaughtError!==void 0&&(o=t.onUncaughtError),t.onCaughtError!==void 0&&(c=t.onCaughtError),t.onRecoverableError!==void 0&&(d=t.onRecoverableError),t.unstable_transitionCallbacks!==void 0&&(b=t.unstable_transitionCallbacks)),t=N0(e,1,!1,null,null,n,l,o,c,d,b,null),e[ll]=t.current,Kc(e.nodeType===8?e.parentNode:e),new hs(t)},Gr.hydrateRoot=function(e,t,n){if(!s(e))throw Error(u(299));var l=!1,o="",c=oh,d=uh,b=ch,x=null,_=null;return n!=null&&(n.unstable_strictMode===!0&&(l=!0),n.identifierPrefix!==void 0&&(o=n.identifierPrefix),n.onUncaughtError!==void 0&&(c=n.onUncaughtError),n.onCaughtError!==void 0&&(d=n.onCaughtError),n.onRecoverableError!==void 0&&(b=n.onRecoverableError),n.unstable_transitionCallbacks!==void 0&&(x=n.unstable_transitionCallbacks),n.formState!==void 0&&(_=n.formState)),t=N0(e,1,!0,t,n??null,l,o,c,d,b,x,_),t.context=U0(null),n=t.current,l=Ht(),o=ra(l),o.callback=null,ia(n,o,l),t.current.lanes=l,Fl(t,l),gn(t),e[ll]=t.current,Kc(e),new Oo(t)},Gr.version="19.0.0",Gr}var I0;function xg(){if(I0)return vs.exports;I0=1;function a(){if(!(typeof __REACT_DEVTOOLS_GLOBAL_HOOK__>"u"||typeof __REACT_DEVTOOLS_GLOBAL_HOOK__.checkDCE!="function"))try{__REACT_DEVTOOLS_GLOBAL_HOOK__.checkDCE(a)}catch(r){console.error(r)}}return a(),vs.exports=yg(),vs.exports}var $5=xg(),em="popstate";function Sg(a={}){function r(u,s){let{pathname:f,search:m,hash:v}=u.location;return Ds("",{pathname:f,search:m,hash:v},s.state&&s.state.usr||null,s.state&&s.state.key||"default")}function i(u,s){return typeof s=="string"?s:ni(s)}return Og(r,i,null,a)}function Ye(a,r){if(a===!1||a===null||typeof a>"u")throw new Error(r)}function Wt(a,r){if(!a){typeof console<"u"&&console.warn(r);try{throw new Error(r)}catch{}}}function Eg(){return Math.random().toString(36).substring(2,10)}function tm(a,r){return{usr:a.state,key:a.key,idx:r}}function Ds(a,r,i=null,u){return{pathname:typeof a=="string"?a:a.pathname,search:"",hash:"",...typeof r=="string"?Xl(r):r,state:i,key:r&&r.key||u||Eg()}}function ni({pathname:a="/",search:r="",hash:i=""}){return r&&r!=="?"&&(a+=r.charAt(0)==="?"?r:"?"+r),i&&i!=="#"&&(a+=i.charAt(0)==="#"?i:"#"+i),a}function Xl(a){let r={};if(a){let i=a.indexOf("#");i>=0&&(r.hash=a.substring(i),a=a.substring(0,i));let u=a.indexOf("?");u>=0&&(r.search=a.substring(u),a=a.substring(0,u)),a&&(r.pathname=a)}return r}function Og(a,r,i,u={}){let{window:s=document.defaultView,v5Compat:f=!1}=u,m=s.history,v="POP",h=null,p=g();p==null&&(p=0,m.replaceState({...m.state,idx:p},""));function g(){return(m.state||{idx:null}).idx}function z(){v="POP";let R=g(),q=R==null?null:R-p;p=R,h&&h({action:v,location:E.location,delta:q})}function M(R,q){v="PUSH";let N=Ds(E.location,R,q);p=g()+1;let V=tm(N,p),F=E.createHref(N);try{m.pushState(V,"",F)}catch($){if($ instanceof DOMException&&$.name==="DataCloneError")throw $;s.location.assign(F)}f&&h&&h({action:v,location:E.location,delta:1})}function w(R,q){v="REPLACE";let N=Ds(E.location,R,q);p=g();let V=tm(N,p),F=E.createHref(N);m.replaceState(V,"",F),f&&h&&h({action:v,location:E.location,delta:0})}function A(R){let q=s.location.origin!=="null"?s.location.origin:s.location.href,N=typeof R=="string"?R:ni(R);return N=N.replace(/ $/,"%20"),Ye(q,`No window.location.(origin|href) available to create URL for href: ${N}`),new URL(N,q)}let E={get action(){return v},get location(){return a(s,m)},listen(R){if(h)throw new Error("A history only accepts one active listener");return s.addEventListener(em,z),h=R,()=>{s.removeEventListener(em,z),h=null}},createHref(R){return r(s,R)},createURL:A,encodeLocation(R){let q=A(R);return{pathname:q.pathname,search:q.search,hash:q.hash}},push:M,replace:w,go(R){return m.go(R)}};return E}function Vm(a,r,i="/"){return Ag(a,r,i,!1)}function Ag(a,r,i,u){let s=typeof r=="string"?Xl(r):r,f=$n(s.pathname||"/",i);if(f==null)return null;let m=jm(a);Tg(m);let v=null;for(let h=0;v==null&&h<m.length;++h){let p=qg(f);v=Ng(m[h],p,u)}return v}function jm(a,r=[],i=[],u=""){let s=(f,m,v)=>{let h={relativePath:v===void 0?f.path||"":v,caseSensitive:f.caseSensitive===!0,childrenIndex:m,route:f};h.relativePath.startsWith("/")&&(Ye(h.relativePath.startsWith(u),`Absolute route path "${h.relativePath}" nested under path "${u}" is not valid. An absolute child route path must start with the combined path of all its parent routes.`),h.relativePath=h.relativePath.slice(u.length));let p=Qn([u,h.relativePath]),g=i.concat(h);f.children&&f.children.length>0&&(Ye(f.index!==!0,`Index routes must not have child routes. Please remove all child routes from route path "${p}".`),jm(f.children,r,g,p)),!(f.path==null&&!f.index)&&r.push({path:p,score:Cg(p,f.index),routesMeta:g})};return a.forEach((f,m)=>{var v;if(f.path===""||!((v=f.path)!=null&&v.includes("?")))s(f,m);else for(let h of Ym(f.path))s(f,m,h)}),r}function Ym(a){let r=a.split("/");if(r.length===0)return[];let[i,...u]=r,s=i.endsWith("?"),f=i.replace(/\?$/,"");if(u.length===0)return s?[f,""]:[f];let m=Ym(u.join("/")),v=[];return v.push(...m.map(h=>h===""?f:[f,h].join("/"))),s&&v.push(...m),v.map(h=>a.startsWith("/")&&h===""?"/":h)}function Tg(a){a.sort((r,i)=>r.score!==i.score?i.score-r.score:kg(r.routesMeta.map(u=>u.childrenIndex),i.routesMeta.map(u=>u.childrenIndex)))}var _g=/^:[\w-]+$/,zg=3,Dg=2,wg=1,Rg=10,Mg=-2,nm=a=>a==="*";function Cg(a,r){let i=a.split("/"),u=i.length;return i.some(nm)&&(u+=Mg),r&&(u+=Dg),i.filter(s=>!nm(s)).reduce((s,f)=>s+(_g.test(f)?zg:f===""?wg:Rg),u)}function kg(a,r){return a.length===r.length&&a.slice(0,-1).every((u,s)=>u===r[s])?a[a.length-1]-r[r.length-1]:0}function Ng(a,r,i=!1){let{routesMeta:u}=a,s={},f="/",m=[];for(let v=0;v<u.length;++v){let h=u[v],p=v===u.length-1,g=f==="/"?r:r.slice(f.length)||"/",z=Ho({path:h.relativePath,caseSensitive:h.caseSensitive,end:p},g),M=h.route;if(!z&&p&&i&&!u[u.length-1].route.index&&(z=Ho({path:h.relativePath,caseSensitive:h.caseSensitive,end:!1},g)),!z)return null;Object.assign(s,z.params),m.push({params:s,pathname:Qn([f,z.pathname]),pathnameBase:Vg(Qn([f,z.pathnameBase])),route:M}),z.pathnameBase!=="/"&&(f=Qn([f,z.pathnameBase]))}return m}function Ho(a,r){typeof a=="string"&&(a={path:a,caseSensitive:!1,end:!0});let[i,u]=Ug(a.path,a.caseSensitive,a.end),s=r.match(i);if(!s)return null;let f=s[0],m=f.replace(/(.)\/+$/,"$1"),v=s.slice(1);return{params:u.reduce((p,{paramName:g,isOptional:z},M)=>{if(g==="*"){let A=v[M]||"";m=f.slice(0,f.length-A.length).replace(/(.)\/+$/,"$1")}const w=v[M];return z&&!w?p[g]=void 0:p[g]=(w||"").replace(/%2F/g,"/"),p},{}),pathname:f,pathnameBase:m,pattern:a}}function Ug(a,r=!1,i=!0){Wt(a==="*"||!a.endsWith("*")||a.endsWith("/*"),`Route path "${a}" will be treated as if it were "${a.replace(/\*$/,"/*")}" because the \`*\` character must always follow a \`/\` in the pattern. To get rid of this warning, please change the route path to "${a.replace(/\*$/,"/*")}".`);let u=[],s="^"+a.replace(/\/*\*?$/,"").replace(/^\/*/,"/").replace(/[\\.*+^${}|()[\]]/g,"\\$&").replace(/\/:([\w-]+)(\?)?/g,(m,v,h)=>(u.push({paramName:v,isOptional:h!=null}),h?"/?([^\\/]+)?":"/([^\\/]+)"));return a.endsWith("*")?(u.push({paramName:"*"}),s+=a==="*"||a==="/*"?"(.*)$":"(?:\\/(.+)|\\/*)$"):i?s+="\\/*$":a!==""&&a!=="/"&&(s+="(?:(?=\\/|$))"),[new RegExp(s,r?void 0:"i"),u]}function qg(a){try{return a.split("/").map(r=>decodeURIComponent(r).replace(/\//g,"%2F")).join("/")}catch(r){return Wt(!1,`The URL path "${a}" could not be decoded because it is a malformed URL segment. This is probably due to a bad percent encoding (${r}).`),a}}function $n(a,r){if(r==="/")return a;if(!a.toLowerCase().startsWith(r.toLowerCase()))return null;let i=r.endsWith("/")?r.length-1:r.length,u=a.charAt(i);return u&&u!=="/"?null:a.slice(i)||"/"}function Hg(a,r="/"){let{pathname:i,search:u="",hash:s=""}=typeof a=="string"?Xl(a):a;return{pathname:i?i.startsWith("/")?i:Lg(i,r):r,search:jg(u),hash:Yg(s)}}function Lg(a,r){let i=r.replace(/\/+$/,"").split("/");return a.split("/").forEach(s=>{s===".."?i.length>1&&i.pop():s!=="."&&i.push(s)}),i.length>1?i.join("/"):"/"}function gs(a,r,i,u){return`Cannot include a '${a}' character in a manually specified \`to.${r}\` field [${JSON.stringify(u)}].  Please separate it out to the \`to.${i}\` field. Alternatively you may provide the full path as a string in <Link to="..."> and the router will parse it for you.`}function Bg(a){return a.filter((r,i)=>i===0||r.route.path&&r.route.path.length>0)}function Gs(a){let r=Bg(a);return r.map((i,u)=>u===r.length-1?i.pathname:i.pathnameBase)}function Qs(a,r,i,u=!1){let s;typeof a=="string"?s=Xl(a):(s={...a},Ye(!s.pathname||!s.pathname.includes("?"),gs("?","pathname","search",s)),Ye(!s.pathname||!s.pathname.includes("#"),gs("#","pathname","hash",s)),Ye(!s.search||!s.search.includes("#"),gs("#","search","hash",s)));let f=a===""||s.pathname==="",m=f?"/":s.pathname,v;if(m==null)v=i;else{let z=r.length-1;if(!u&&m.startsWith("..")){let M=m.split("/");for(;M[0]==="..";)M.shift(),z-=1;s.pathname=M.join("/")}v=z>=0?r[z]:"/"}let h=Hg(s,v),p=m&&m!=="/"&&m.endsWith("/"),g=(f||m===".")&&i.endsWith("/");return!h.pathname.endsWith("/")&&(p||g)&&(h.pathname+="/"),h}var Qn=a=>a.join("/").replace(/\/\/+/g,"/"),Vg=a=>a.replace(/\/+$/,"").replace(/^\/*/,"/"),jg=a=>!a||a==="?"?"":a.startsWith("?")?a:"?"+a,Yg=a=>!a||a==="#"?"":a.startsWith("#")?a:"#"+a;function Xg(a){return a!=null&&typeof a.status=="number"&&typeof a.statusText=="string"&&typeof a.internal=="boolean"&&"data"in a}var Xm=["POST","PUT","PATCH","DELETE"];new Set(Xm);var Gg=["GET",...Xm];new Set(Gg);var Gl=y.createContext(null);Gl.displayName="DataRouter";var Fo=y.createContext(null);Fo.displayName="DataRouterState";var Gm=y.createContext({isTransitioning:!1});Gm.displayName="ViewTransition";var Qg=y.createContext(new Map);Qg.displayName="Fetchers";var Zg=y.createContext(null);Zg.displayName="Await";var dn=y.createContext(null);dn.displayName="Navigation";var ai=y.createContext(null);ai.displayName="Location";var It=y.createContext({outlet:null,matches:[],isDataRoute:!1});It.displayName="Route";var Zs=y.createContext(null);Zs.displayName="RouteError";function $g(a,{relative:r}={}){Ye(Ql(),"useHref() may be used only in the context of a <Router> component.");let{basename:i,navigator:u}=y.useContext(dn),{hash:s,pathname:f,search:m}=li(a,{relative:r}),v=f;return i!=="/"&&(v=f==="/"?i:Qn([i,f])),u.createHref({pathname:v,search:m,hash:s})}function Ql(){return y.useContext(ai)!=null}function Pn(){return Ye(Ql(),"useLocation() may be used only in the context of a <Router> component."),y.useContext(ai).location}var Qm="You should call navigate() in a React.useEffect(), not when your component is first rendered.";function Zm(a){y.useContext(dn).static||y.useLayoutEffect(a)}function $s(){let{isDataRoute:a}=y.useContext(It);return a?uy():Pg()}function Pg(){Ye(Ql(),"useNavigate() may be used only in the context of a <Router> component.");let a=y.useContext(Gl),{basename:r,navigator:i}=y.useContext(dn),{matches:u}=y.useContext(It),{pathname:s}=Pn(),f=JSON.stringify(Gs(u)),m=y.useRef(!1);return Zm(()=>{m.current=!0}),y.useCallback((h,p={})=>{if(Wt(m.current,Qm),!m.current)return;if(typeof h=="number"){i.go(h);return}let g=Qs(h,JSON.parse(f),s,p.relative==="path");a==null&&r!=="/"&&(g.pathname=g.pathname==="/"?r:Qn([r,g.pathname])),(p.replace?i.replace:i.push)(g,p.state,p)},[r,i,f,s,a])}var Fg=y.createContext(null);function Kg(a){let r=y.useContext(It).outlet;return r&&y.createElement(Fg.Provider,{value:a},r)}function P5(){let{matches:a}=y.useContext(It),r=a[a.length-1];return r?r.params:{}}function li(a,{relative:r}={}){let{matches:i}=y.useContext(It),{pathname:u}=Pn(),s=JSON.stringify(Gs(i));return y.useMemo(()=>Qs(a,JSON.parse(s),u,r==="path"),[a,s,u,r])}function Jg(a,r){return $m(a,r)}function $m(a,r,i,u){var N;Ye(Ql(),"useRoutes() may be used only in the context of a <Router> component.");let{navigator:s,static:f}=y.useContext(dn),{matches:m}=y.useContext(It),v=m[m.length-1],h=v?v.params:{},p=v?v.pathname:"/",g=v?v.pathnameBase:"/",z=v&&v.route;{let V=z&&z.path||"";Pm(p,!z||V.endsWith("*")||V.endsWith("*?"),`You rendered descendant <Routes> (or called \`useRoutes()\`) at "${p}" (under <Route path="${V}">) but the parent route path has no trailing "*". This means if you navigate deeper, the parent won't match anymore and therefore the child routes will never render.

Please change the parent <Route path="${V}"> to <Route path="${V==="/"?"*":`${V}/*`}">.`)}let M=Pn(),w;if(r){let V=typeof r=="string"?Xl(r):r;Ye(g==="/"||((N=V.pathname)==null?void 0:N.startsWith(g)),`When overriding the location using \`<Routes location>\` or \`useRoutes(routes, location)\`, the location pathname must begin with the portion of the URL pathname that was matched by all parent routes. The current pathname base is "${g}" but pathname "${V.pathname}" was given in the \`location\` prop.`),w=V}else w=M;let A=w.pathname||"/",E=A;if(g!=="/"){let V=g.replace(/^\//,"").split("/");E="/"+A.replace(/^\//,"").split("/").slice(V.length).join("/")}let R=!f&&i&&i.matches&&i.matches.length>0?i.matches:Vm(a,{pathname:E});Wt(z||R!=null,`No routes matched location "${w.pathname}${w.search}${w.hash}" `),Wt(R==null||R[R.length-1].route.element!==void 0||R[R.length-1].route.Component!==void 0||R[R.length-1].route.lazy!==void 0,`Matched leaf route at location "${w.pathname}${w.search}${w.hash}" does not have an element or Component. This means it will render an <Outlet /> with a null value by default resulting in an "empty" page.`);let q=ny(R&&R.map(V=>Object.assign({},V,{params:Object.assign({},h,V.params),pathname:Qn([g,s.encodeLocation?s.encodeLocation(V.pathname).pathname:V.pathname]),pathnameBase:V.pathnameBase==="/"?g:Qn([g,s.encodeLocation?s.encodeLocation(V.pathnameBase).pathname:V.pathnameBase])})),m,i,u);return r&&q?y.createElement(ai.Provider,{value:{location:{pathname:"/",search:"",hash:"",state:null,key:"default",...w},navigationType:"POP"}},q):q}function Wg(){let a=oy(),r=Xg(a)?`${a.status} ${a.statusText}`:a instanceof Error?a.message:JSON.stringify(a),i=a instanceof Error?a.stack:null,u="rgba(200,200,200, 0.5)",s={padding:"0.5rem",backgroundColor:u},f={padding:"2px 4px",backgroundColor:u},m=null;return console.error("Error handled by React Router default ErrorBoundary:",a),m=y.createElement(y.Fragment,null,y.createElement("p",null,"💿 Hey developer 👋"),y.createElement("p",null,"You can provide a way better UX than this when your app throws errors by providing your own ",y.createElement("code",{style:f},"ErrorBoundary")," or"," ",y.createElement("code",{style:f},"errorElement")," prop on your route.")),y.createElement(y.Fragment,null,y.createElement("h2",null,"Unexpected Application Error!"),y.createElement("h3",{style:{fontStyle:"italic"}},r),i?y.createElement("pre",{style:s},i):null,m)}var Ig=y.createElement(Wg,null),ey=class extends y.Component{constructor(a){super(a),this.state={location:a.location,revalidation:a.revalidation,error:a.error}}static getDerivedStateFromError(a){return{error:a}}static getDerivedStateFromProps(a,r){return r.location!==a.location||r.revalidation!=="idle"&&a.revalidation==="idle"?{error:a.error,location:a.location,revalidation:a.revalidation}:{error:a.error!==void 0?a.error:r.error,location:r.location,revalidation:a.revalidation||r.revalidation}}componentDidCatch(a,r){console.error("React Router caught the following error during render",a,r)}render(){return this.state.error!==void 0?y.createElement(It.Provider,{value:this.props.routeContext},y.createElement(Zs.Provider,{value:this.state.error,children:this.props.component})):this.props.children}};function ty({routeContext:a,match:r,children:i}){let u=y.useContext(Gl);return u&&u.static&&u.staticContext&&(r.route.errorElement||r.route.ErrorBoundary)&&(u.staticContext._deepestRenderedBoundaryId=r.route.id),y.createElement(It.Provider,{value:a},i)}function ny(a,r=[],i=null,u=null){if(a==null){if(!i)return null;if(i.errors)a=i.matches;else if(r.length===0&&!i.initialized&&i.matches.length>0)a=i.matches;else return null}let s=a,f=i==null?void 0:i.errors;if(f!=null){let h=s.findIndex(p=>p.route.id&&(f==null?void 0:f[p.route.id])!==void 0);Ye(h>=0,`Could not find a matching route for errors on route IDs: ${Object.keys(f).join(",")}`),s=s.slice(0,Math.min(s.length,h+1))}let m=!1,v=-1;if(i)for(let h=0;h<s.length;h++){let p=s[h];if((p.route.HydrateFallback||p.route.hydrateFallbackElement)&&(v=h),p.route.id){let{loaderData:g,errors:z}=i,M=p.route.loader&&!g.hasOwnProperty(p.route.id)&&(!z||z[p.route.id]===void 0);if(p.route.lazy||M){m=!0,v>=0?s=s.slice(0,v+1):s=[s[0]];break}}}return s.reduceRight((h,p,g)=>{let z,M=!1,w=null,A=null;i&&(z=f&&p.route.id?f[p.route.id]:void 0,w=p.route.errorElement||Ig,m&&(v<0&&g===0?(Pm("route-fallback",!1,"No `HydrateFallback` element provided to render during initial hydration"),M=!0,A=null):v===g&&(M=!0,A=p.route.hydrateFallbackElement||null)));let E=r.concat(s.slice(0,g+1)),R=()=>{let q;return z?q=w:M?q=A:p.route.Component?q=y.createElement(p.route.Component,null):p.route.element?q=p.route.element:q=h,y.createElement(ty,{match:p,routeContext:{outlet:h,matches:E,isDataRoute:i!=null},children:q})};return i&&(p.route.ErrorBoundary||p.route.errorElement||g===0)?y.createElement(ey,{location:i.location,revalidation:i.revalidation,component:w,error:z,children:R(),routeContext:{outlet:null,matches:E,isDataRoute:!0}}):R()},null)}function Ps(a){return`${a} must be used within a data router.  See https://reactrouter.com/en/main/routers/picking-a-router.`}function ay(a){let r=y.useContext(Gl);return Ye(r,Ps(a)),r}function ly(a){let r=y.useContext(Fo);return Ye(r,Ps(a)),r}function ry(a){let r=y.useContext(It);return Ye(r,Ps(a)),r}function Fs(a){let r=ry(a),i=r.matches[r.matches.length-1];return Ye(i.route.id,`${a} can only be used on routes that contain a unique "id"`),i.route.id}function iy(){return Fs("useRouteId")}function oy(){var u;let a=y.useContext(Zs),r=ly("useRouteError"),i=Fs("useRouteError");return a!==void 0?a:(u=r.errors)==null?void 0:u[i]}function uy(){let{router:a}=ay("useNavigate"),r=Fs("useNavigate"),i=y.useRef(!1);return Zm(()=>{i.current=!0}),y.useCallback(async(s,f={})=>{Wt(i.current,Qm),i.current&&(typeof s=="number"?a.navigate(s):await a.navigate(s,{fromRouteId:r,...f}))},[a,r])}var am={};function Pm(a,r,i){!r&&!am[a]&&(am[a]=!0,Wt(!1,i))}y.memo(cy);function cy({routes:a,future:r,state:i}){return $m(a,void 0,i,r)}function F5({to:a,replace:r,state:i,relative:u}){Ye(Ql(),"<Navigate> may be used only in the context of a <Router> component.");let{static:s}=y.useContext(dn);Wt(!s,"<Navigate> must not be used on the initial render in a <StaticRouter>. This is a no-op, but you should modify your code so the <Navigate> is only ever rendered in response to some user interaction or state change.");let{matches:f}=y.useContext(It),{pathname:m}=Pn(),v=$s(),h=Qs(a,Gs(f),m,u==="path"),p=JSON.stringify(h);return y.useEffect(()=>{v(JSON.parse(p),{replace:r,state:i,relative:u})},[v,p,u,r,i]),null}function K5(a){return Kg(a.context)}function sy(a){Ye(!1,"A <Route> is only ever to be used as the child of <Routes> element, never rendered directly. Please wrap your <Route> in a <Routes>.")}function fy({basename:a="/",children:r=null,location:i,navigationType:u="POP",navigator:s,static:f=!1}){Ye(!Ql(),"You cannot render a <Router> inside another <Router>. You should never have more than one in your app.");let m=a.replace(/^\/*/,"/"),v=y.useMemo(()=>({basename:m,navigator:s,static:f,future:{}}),[m,s,f]);typeof i=="string"&&(i=Xl(i));let{pathname:h="/",search:p="",hash:g="",state:z=null,key:M="default"}=i,w=y.useMemo(()=>{let A=$n(h,m);return A==null?null:{location:{pathname:A,search:p,hash:g,state:z,key:M},navigationType:u}},[m,h,p,g,z,M,u]);return Wt(w!=null,`<Router basename="${m}"> is not able to match the URL "${h}${p}${g}" because it does not start with the basename, so the <Router> won't render anything.`),w==null?null:y.createElement(dn.Provider,{value:v},y.createElement(ai.Provider,{children:r,value:w}))}function J5({children:a,location:r}){return Jg(ws(a),r)}function ws(a,r=[]){let i=[];return y.Children.forEach(a,(u,s)=>{if(!y.isValidElement(u))return;let f=[...r,s];if(u.type===y.Fragment){i.push.apply(i,ws(u.props.children,f));return}Ye(u.type===sy,`[${typeof u.type=="string"?u.type:u.type.name}] is not a <Route> component. All component children of <Routes> must be a <Route> or <React.Fragment>`),Ye(!u.props.index||!u.props.children,"An index route cannot have child routes.");let m={id:u.props.id||f.join("-"),caseSensitive:u.props.caseSensitive,element:u.props.element,Component:u.props.Component,index:u.props.index,path:u.props.path,loader:u.props.loader,action:u.props.action,hydrateFallbackElement:u.props.hydrateFallbackElement,HydrateFallback:u.props.HydrateFallback,errorElement:u.props.errorElement,ErrorBoundary:u.props.ErrorBoundary,hasErrorBoundary:u.props.hasErrorBoundary===!0||u.props.ErrorBoundary!=null||u.props.errorElement!=null,shouldRevalidate:u.props.shouldRevalidate,handle:u.props.handle,lazy:u.props.lazy};u.props.children&&(m.children=ws(u.props.children,f)),i.push(m)}),i}var No="get",Uo="application/x-www-form-urlencoded";function Ko(a){return a!=null&&typeof a.tagName=="string"}function dy(a){return Ko(a)&&a.tagName.toLowerCase()==="button"}function hy(a){return Ko(a)&&a.tagName.toLowerCase()==="form"}function my(a){return Ko(a)&&a.tagName.toLowerCase()==="input"}function py(a){return!!(a.metaKey||a.altKey||a.ctrlKey||a.shiftKey)}function vy(a,r){return a.button===0&&(!r||r==="_self")&&!py(a)}function Rs(a=""){return new URLSearchParams(typeof a=="string"||Array.isArray(a)||a instanceof URLSearchParams?a:Object.keys(a).reduce((r,i)=>{let u=a[i];return r.concat(Array.isArray(u)?u.map(s=>[i,s]):[[i,u]])},[]))}function by(a,r){let i=Rs(a);return r&&r.forEach((u,s)=>{i.has(s)||r.getAll(s).forEach(f=>{i.append(s,f)})}),i}var To=null;function gy(){if(To===null)try{new FormData(document.createElement("form"),0),To=!1}catch{To=!0}return To}var yy=new Set(["application/x-www-form-urlencoded","multipart/form-data","text/plain"]);function ys(a){return a!=null&&!yy.has(a)?(Wt(!1,`"${a}" is not a valid \`encType\` for \`<Form>\`/\`<fetcher.Form>\` and will default to "${Uo}"`),null):a}function xy(a,r){let i,u,s,f,m;if(hy(a)){let v=a.getAttribute("action");u=v?$n(v,r):null,i=a.getAttribute("method")||No,s=ys(a.getAttribute("enctype"))||Uo,f=new FormData(a)}else if(dy(a)||my(a)&&(a.type==="submit"||a.type==="image")){let v=a.form;if(v==null)throw new Error('Cannot submit a <button> or <input type="submit"> without a <form>');let h=a.getAttribute("formaction")||v.getAttribute("action");if(u=h?$n(h,r):null,i=a.getAttribute("formmethod")||v.getAttribute("method")||No,s=ys(a.getAttribute("formenctype"))||ys(v.getAttribute("enctype"))||Uo,f=new FormData(v,a),!gy()){let{name:p,type:g,value:z}=a;if(g==="image"){let M=p?`${p}.`:"";f.append(`${M}x`,"0"),f.append(`${M}y`,"0")}else p&&f.append(p,z)}}else{if(Ko(a))throw new Error('Cannot submit element that is not <form>, <button>, or <input type="submit|image">');i=No,u=null,s=Uo,m=a}return f&&s==="text/plain"&&(m=f,f=void 0),{action:u,method:i.toLowerCase(),encType:s,formData:f,body:m}}function Ks(a,r){if(a===!1||a===null||typeof a>"u")throw new Error(r)}async function Sy(a,r){if(a.id in r)return r[a.id];try{let i=await import(a.module);return r[a.id]=i,i}catch(i){return console.error(`Error loading route module \`${a.module}\`, reloading page...`),console.error(i),window.__reactRouterContext&&window.__reactRouterContext.isSpaMode,window.location.reload(),new Promise(()=>{})}}function Ey(a){return a==null?!1:a.href==null?a.rel==="preload"&&typeof a.imageSrcSet=="string"&&typeof a.imageSizes=="string":typeof a.rel=="string"&&typeof a.href=="string"}async function Oy(a,r,i){let u=await Promise.all(a.map(async s=>{let f=r.routes[s.route.id];if(f){let m=await Sy(f,i);return m.links?m.links():[]}return[]}));return zy(u.flat(1).filter(Ey).filter(s=>s.rel==="stylesheet"||s.rel==="preload").map(s=>s.rel==="stylesheet"?{...s,rel:"prefetch",as:"style"}:{...s,rel:"prefetch"}))}function lm(a,r,i,u,s,f){let m=(h,p)=>i[p]?h.route.id!==i[p].route.id:!0,v=(h,p)=>{var g;return i[p].pathname!==h.pathname||((g=i[p].route.path)==null?void 0:g.endsWith("*"))&&i[p].params["*"]!==h.params["*"]};return f==="assets"?r.filter((h,p)=>m(h,p)||v(h,p)):f==="data"?r.filter((h,p)=>{var z;let g=u.routes[h.route.id];if(!g||!g.hasLoader)return!1;if(m(h,p)||v(h,p))return!0;if(h.route.shouldRevalidate){let M=h.route.shouldRevalidate({currentUrl:new URL(s.pathname+s.search+s.hash,window.origin),currentParams:((z=i[0])==null?void 0:z.params)||{},nextUrl:new URL(a,window.origin),nextParams:h.params,defaultShouldRevalidate:!0});if(typeof M=="boolean")return M}return!0}):[]}function Ay(a,r,{includeHydrateFallback:i}={}){return Ty(a.map(u=>{let s=r.routes[u.route.id];if(!s)return[];let f=[s.module];return s.clientActionModule&&(f=f.concat(s.clientActionModule)),s.clientLoaderModule&&(f=f.concat(s.clientLoaderModule)),i&&s.hydrateFallbackModule&&(f=f.concat(s.hydrateFallbackModule)),s.imports&&(f=f.concat(s.imports)),f}).flat(1))}function Ty(a){return[...new Set(a)]}function _y(a){let r={},i=Object.keys(a).sort();for(let u of i)r[u]=a[u];return r}function zy(a,r){let i=new Set;return new Set(r),a.reduce((u,s)=>{let f=JSON.stringify(_y(s));return i.has(f)||(i.add(f),u.push({key:f,link:s})),u},[])}var Dy=new Set([100,101,204,205]);function wy(a,r){let i=typeof a=="string"?new URL(a,typeof window>"u"?"server://singlefetch/":window.location.origin):a;return i.pathname==="/"?i.pathname="_root.data":r&&$n(i.pathname,r)==="/"?i.pathname=`${r.replace(/\/$/,"")}/_root.data`:i.pathname=`${i.pathname.replace(/\/$/,"")}.data`,i}function Fm(){let a=y.useContext(Gl);return Ks(a,"You must render this element inside a <DataRouterContext.Provider> element"),a}function Ry(){let a=y.useContext(Fo);return Ks(a,"You must render this element inside a <DataRouterStateContext.Provider> element"),a}var Js=y.createContext(void 0);Js.displayName="FrameworkContext";function Km(){let a=y.useContext(Js);return Ks(a,"You must render this element inside a <HydratedRouter> element"),a}function My(a,r){let i=y.useContext(Js),[u,s]=y.useState(!1),[f,m]=y.useState(!1),{onFocus:v,onBlur:h,onMouseEnter:p,onMouseLeave:g,onTouchStart:z}=r,M=y.useRef(null);y.useEffect(()=>{if(a==="render"&&m(!0),a==="viewport"){let E=q=>{q.forEach(N=>{m(N.isIntersecting)})},R=new IntersectionObserver(E,{threshold:.5});return M.current&&R.observe(M.current),()=>{R.disconnect()}}},[a]),y.useEffect(()=>{if(u){let E=setTimeout(()=>{m(!0)},100);return()=>{clearTimeout(E)}}},[u]);let w=()=>{s(!0)},A=()=>{s(!1),m(!1)};return i?a!=="intent"?[f,M,{}]:[f,M,{onFocus:Qr(v,w),onBlur:Qr(h,A),onMouseEnter:Qr(p,w),onMouseLeave:Qr(g,A),onTouchStart:Qr(z,w)}]:[!1,M,{}]}function Qr(a,r){return i=>{a&&a(i),i.defaultPrevented||r(i)}}function Cy({page:a,...r}){let{router:i}=Fm(),u=y.useMemo(()=>Vm(i.routes,a,i.basename),[i.routes,a,i.basename]);return u?y.createElement(Ny,{page:a,matches:u,...r}):null}function ky(a){let{manifest:r,routeModules:i}=Km(),[u,s]=y.useState([]);return y.useEffect(()=>{let f=!1;return Oy(a,r,i).then(m=>{f||s(m)}),()=>{f=!0}},[a,r,i]),u}function Ny({page:a,matches:r,...i}){let u=Pn(),{manifest:s,routeModules:f}=Km(),{basename:m}=Fm(),{loaderData:v,matches:h}=Ry(),p=y.useMemo(()=>lm(a,r,h,s,u,"data"),[a,r,h,s,u]),g=y.useMemo(()=>lm(a,r,h,s,u,"assets"),[a,r,h,s,u]),z=y.useMemo(()=>{if(a===u.pathname+u.search+u.hash)return[];let A=new Set,E=!1;if(r.forEach(q=>{var V;let N=s.routes[q.route.id];!N||!N.hasLoader||(!p.some(F=>F.route.id===q.route.id)&&q.route.id in v&&((V=f[q.route.id])!=null&&V.shouldRevalidate)||N.hasClientLoader?E=!0:A.add(q.route.id))}),A.size===0)return[];let R=wy(a,m);return E&&A.size>0&&R.searchParams.set("_routes",r.filter(q=>A.has(q.route.id)).map(q=>q.route.id).join(",")),[R.pathname+R.search]},[m,v,u,s,p,r,a,f]),M=y.useMemo(()=>Ay(g,s),[g,s]),w=ky(g);return y.createElement(y.Fragment,null,z.map(A=>y.createElement("link",{key:A,rel:"prefetch",as:"fetch",href:A,...i})),M.map(A=>y.createElement("link",{key:A,rel:"modulepreload",href:A,...i})),w.map(({key:A,link:E})=>y.createElement("link",{key:A,...E})))}function Uy(...a){return r=>{a.forEach(i=>{typeof i=="function"?i(r):i!=null&&(i.current=r)})}}var Jm=typeof window<"u"&&typeof window.document<"u"&&typeof window.document.createElement<"u";try{Jm&&(window.__reactRouterVersion="7.5.3")}catch{}function W5({basename:a,children:r,window:i}){let u=y.useRef();u.current==null&&(u.current=Sg({window:i,v5Compat:!0}));let s=u.current,[f,m]=y.useState({action:s.action,location:s.location}),v=y.useCallback(h=>{y.startTransition(()=>m(h))},[m]);return y.useLayoutEffect(()=>s.listen(v),[s,v]),y.createElement(fy,{basename:a,children:r,location:f.location,navigationType:f.action,navigator:s})}var Wm=/^(?:[a-z][a-z0-9+.-]*:|\/\/)/i,Im=y.forwardRef(function({onClick:r,discover:i="render",prefetch:u="none",relative:s,reloadDocument:f,replace:m,state:v,target:h,to:p,preventScrollReset:g,viewTransition:z,...M},w){let{basename:A}=y.useContext(dn),E=typeof p=="string"&&Wm.test(p),R,q=!1;if(typeof p=="string"&&E&&(R=p,Jm))try{let G=new URL(window.location.href),W=p.startsWith("//")?new URL(G.protocol+p):new URL(p),ce=$n(W.pathname,A);W.origin===G.origin&&ce!=null?p=ce+W.search+W.hash:q=!0}catch{Wt(!1,`<Link to="${p}"> contains an invalid URL which will probably break when clicked - please update to a valid URL path.`)}let N=$g(p,{relative:s}),[V,F,$]=My(u,M),me=By(p,{replace:m,state:v,target:h,preventScrollReset:g,relative:s,viewTransition:z});function pe(G){r&&r(G),G.defaultPrevented||me(G)}let ve=y.createElement("a",{...M,...$,href:R||N,onClick:q||f?r:pe,ref:Uy(w,F),target:h,"data-discover":!E&&i==="render"?"true":void 0});return V&&!E?y.createElement(y.Fragment,null,ve,y.createElement(Cy,{page:N})):ve});Im.displayName="Link";var qy=y.forwardRef(function({"aria-current":r="page",caseSensitive:i=!1,className:u="",end:s=!1,style:f,to:m,viewTransition:v,children:h,...p},g){let z=li(m,{relative:p.relative}),M=Pn(),w=y.useContext(Fo),{navigator:A,basename:E}=y.useContext(dn),R=w!=null&&Gy(z)&&v===!0,q=A.encodeLocation?A.encodeLocation(z).pathname:z.pathname,N=M.pathname,V=w&&w.navigation&&w.navigation.location?w.navigation.location.pathname:null;i||(N=N.toLowerCase(),V=V?V.toLowerCase():null,q=q.toLowerCase()),V&&E&&(V=$n(V,E)||V);const F=q!=="/"&&q.endsWith("/")?q.length-1:q.length;let $=N===q||!s&&N.startsWith(q)&&N.charAt(F)==="/",me=V!=null&&(V===q||!s&&V.startsWith(q)&&V.charAt(q.length)==="/"),pe={isActive:$,isPending:me,isTransitioning:R},ve=$?r:void 0,G;typeof u=="function"?G=u(pe):G=[u,$?"active":null,me?"pending":null,R?"transitioning":null].filter(Boolean).join(" ");let W=typeof f=="function"?f(pe):f;return y.createElement(Im,{...p,"aria-current":ve,className:G,ref:g,style:W,to:m,viewTransition:v},typeof h=="function"?h(pe):h)});qy.displayName="NavLink";var Hy=y.forwardRef(({discover:a="render",fetcherKey:r,navigate:i,reloadDocument:u,replace:s,state:f,method:m=No,action:v,onSubmit:h,relative:p,preventScrollReset:g,viewTransition:z,...M},w)=>{let A=Yy(),E=Xy(v,{relative:p}),R=m.toLowerCase()==="get"?"get":"post",q=typeof v=="string"&&Wm.test(v),N=V=>{if(h&&h(V),V.defaultPrevented)return;V.preventDefault();let F=V.nativeEvent.submitter,$=(F==null?void 0:F.getAttribute("formmethod"))||m;A(F||V.currentTarget,{fetcherKey:r,method:$,navigate:i,replace:s,state:f,relative:p,preventScrollReset:g,viewTransition:z})};return y.createElement("form",{ref:w,method:R,action:E,onSubmit:u?h:N,...M,"data-discover":!q&&a==="render"?"true":void 0})});Hy.displayName="Form";function Ly(a){return`${a} must be used within a data router.  See https://reactrouter.com/en/main/routers/picking-a-router.`}function ep(a){let r=y.useContext(Gl);return Ye(r,Ly(a)),r}function By(a,{target:r,replace:i,state:u,preventScrollReset:s,relative:f,viewTransition:m}={}){let v=$s(),h=Pn(),p=li(a,{relative:f});return y.useCallback(g=>{if(vy(g,r)){g.preventDefault();let z=i!==void 0?i:ni(h)===ni(p);v(a,{replace:z,state:u,preventScrollReset:s,relative:f,viewTransition:m})}},[h,v,p,i,u,r,a,s,f,m])}function I5(a){Wt(typeof URLSearchParams<"u","You cannot use the `useSearchParams` hook in a browser that does not support the URLSearchParams API. If you need to support Internet Explorer 11, we recommend you load a polyfill such as https://github.com/ungap/url-search-params.");let r=y.useRef(Rs(a)),i=y.useRef(!1),u=Pn(),s=y.useMemo(()=>by(u.search,i.current?null:r.current),[u.search]),f=$s(),m=y.useCallback((v,h)=>{const p=Rs(typeof v=="function"?v(s):v);i.current=!0,f("?"+p,h)},[f,s]);return[s,m]}var Vy=0,jy=()=>`__${String(++Vy)}__`;function Yy(){let{router:a}=ep("useSubmit"),{basename:r}=y.useContext(dn),i=iy();return y.useCallback(async(u,s={})=>{let{action:f,method:m,encType:v,formData:h,body:p}=xy(u,r);if(s.navigate===!1){let g=s.fetcherKey||jy();await a.fetch(g,i,s.action||f,{preventScrollReset:s.preventScrollReset,formData:h,body:p,formMethod:s.method||m,formEncType:s.encType||v,flushSync:s.flushSync})}else await a.navigate(s.action||f,{preventScrollReset:s.preventScrollReset,formData:h,body:p,formMethod:s.method||m,formEncType:s.encType||v,replace:s.replace,state:s.state,fromRouteId:i,flushSync:s.flushSync,viewTransition:s.viewTransition})},[a,r,i])}function Xy(a,{relative:r}={}){let{basename:i}=y.useContext(dn),u=y.useContext(It);Ye(u,"useFormAction must be used inside a RouteContext");let[s]=u.matches.slice(-1),f={...li(a||".",{relative:r})},m=Pn();if(a==null){f.search=m.search;let v=new URLSearchParams(f.search),h=v.getAll("index");if(h.some(g=>g==="")){v.delete("index"),h.filter(z=>z).forEach(z=>v.append("index",z));let g=v.toString();f.search=g?`?${g}`:""}}return(!a||a===".")&&s.route.index&&(f.search=f.search?f.search.replace(/^\?/,"?index&"):"?index"),i!=="/"&&(f.pathname=f.pathname==="/"?i:Qn([i,f.pathname])),ni(f)}function Gy(a,r={}){let i=y.useContext(Gm);Ye(i!=null,"`useViewTransitionState` must be used within `react-router-dom`'s `RouterProvider`.  Did you accidentally import `RouterProvider` from `react-router`?");let{basename:u}=ep("useViewTransitionState"),s=li(a,{relative:r.relative});if(!i.isTransitioning)return!1;let f=$n(i.currentLocation.pathname,u)||i.currentLocation.pathname,m=$n(i.nextLocation.pathname,u)||i.nextLocation.pathname;return Ho(s.pathname,m)!=null||Ho(s.pathname,f)!=null}new TextEncoder;[...Dy];var tp=Bm(),Qy=Object.defineProperty,Zy=(a,r,i)=>r in a?Qy(a,r,{enumerable:!0,configurable:!0,writable:!0,value:i}):a[r]=i,xs=(a,r,i)=>(Zy(a,typeof r!="symbol"?r+"":r,i),i);let $y=class{constructor(){xs(this,"current",this.detect()),xs(this,"handoffState","pending"),xs(this,"currentId",0)}set(r){this.current!==r&&(this.handoffState="pending",this.currentId=0,this.current=r)}reset(){this.set(this.detect())}nextId(){return++this.currentId}get isServer(){return this.current==="server"}get isClient(){return this.current==="client"}detect(){return typeof window>"u"||typeof document>"u"?"server":"client"}handoff(){this.handoffState==="pending"&&(this.handoffState="complete")}get isHandoffComplete(){return this.handoffState==="complete"}},Wa=new $y;function Jo(a){return Wa.isServer?null:a instanceof Node?a.ownerDocument:a!=null&&a.hasOwnProperty("current")&&a.current instanceof Node?a.current.ownerDocument:document}function Wo(a){typeof queueMicrotask=="function"?queueMicrotask(a):Promise.resolve().then(a).catch(r=>setTimeout(()=>{throw r}))}function Oa(){let a=[],r={addEventListener(i,u,s,f){return i.addEventListener(u,s,f),r.add(()=>i.removeEventListener(u,s,f))},requestAnimationFrame(...i){let u=requestAnimationFrame(...i);return r.add(()=>cancelAnimationFrame(u))},nextFrame(...i){return r.requestAnimationFrame(()=>r.requestAnimationFrame(...i))},setTimeout(...i){let u=setTimeout(...i);return r.add(()=>clearTimeout(u))},microTask(...i){let u={current:!0};return Wo(()=>{u.current&&i[0]()}),r.add(()=>{u.current=!1})},style(i,u,s){let f=i.style.getPropertyValue(u);return Object.assign(i.style,{[u]:s}),this.add(()=>{Object.assign(i.style,{[u]:f})})},group(i){let u=Oa();return i(u),this.add(()=>u.dispose())},add(i){return a.includes(i)||a.push(i),()=>{let u=a.indexOf(i);if(u>=0)for(let s of a.splice(u,1))s()}},dispose(){for(let i of a.splice(0))i()}};return r}function Ws(){let[a]=y.useState(Oa);return y.useEffect(()=>()=>a.dispose(),[a]),a}let zt=(a,r)=>{Wa.isServer?y.useEffect(a,r):y.useLayoutEffect(a,r)};function Ia(a){let r=y.useRef(a);return zt(()=>{r.current=a},[a]),r}let Ze=function(a){let r=Ia(a);return X.useCallback((...i)=>r.current(...i),[r])},Py=y.createContext(void 0);function Fy(){return y.useContext(Py)}function Ms(...a){return Array.from(new Set(a.flatMap(r=>typeof r=="string"?r.split(" "):[]))).filter(Boolean).join(" ")}function Ea(a,r,...i){if(a in r){let s=r[a];return typeof s=="function"?s(...i):s}let u=new Error(`Tried to handle "${a}" but there is no handler defined. Only defined handlers are: ${Object.keys(r).map(s=>`"${s}"`).join(", ")}.`);throw Error.captureStackTrace&&Error.captureStackTrace(u,Ea),u}var Lo=(a=>(a[a.None=0]="None",a[a.RenderStrategy=1]="RenderStrategy",a[a.Static=2]="Static",a))(Lo||{}),Sa=(a=>(a[a.Unmount=0]="Unmount",a[a.Hidden=1]="Hidden",a))(Sa||{});function en(){let a=Jy();return y.useCallback(r=>Ky({mergeRefs:a,...r}),[a])}function Ky({ourProps:a,theirProps:r,slot:i,defaultTag:u,features:s,visible:f=!0,name:m,mergeRefs:v}){v=v??Wy;let h=np(r,a);if(f)return _o(h,i,u,m,v);let p=s??0;if(p&2){let{static:g=!1,...z}=h;if(g)return _o(z,i,u,m,v)}if(p&1){let{unmount:g=!0,...z}=h;return Ea(g?0:1,{0(){return null},1(){return _o({...z,hidden:!0,style:{display:"none"}},i,u,m,v)}})}return _o(h,i,u,m,v)}function _o(a,r={},i,u,s){let{as:f=i,children:m,refName:v="ref",...h}=Ss(a,["unmount","static"]),p=a.ref!==void 0?{[v]:a.ref}:{},g=typeof m=="function"?m(r):m;"className"in h&&h.className&&typeof h.className=="function"&&(h.className=h.className(r)),h["aria-labelledby"]&&h["aria-labelledby"]===h.id&&(h["aria-labelledby"]=void 0);let z={};if(r){let M=!1,w=[];for(let[A,E]of Object.entries(r))typeof E=="boolean"&&(M=!0),E===!0&&w.push(A.replace(/([A-Z])/g,R=>`-${R.toLowerCase()}`));if(M){z["data-headlessui-state"]=w.join(" ");for(let A of w)z[`data-${A}`]=""}}if(f===y.Fragment&&(Object.keys(Pa(h)).length>0||Object.keys(Pa(z)).length>0))if(!y.isValidElement(g)||Array.isArray(g)&&g.length>1){if(Object.keys(Pa(h)).length>0)throw new Error(['Passing props on "Fragment"!',"",`The current component <${u} /> is rendering a "Fragment".`,"However we need to passthrough the following props:",Object.keys(Pa(h)).concat(Object.keys(Pa(z))).map(M=>`  - ${M}`).join(`
`),"","You can apply a few solutions:",['Add an `as="..."` prop, to ensure that we render an actual element instead of a "Fragment".',"Render a single element as the child so that we can forward the props onto that element."].map(M=>`  - ${M}`).join(`
`)].join(`
`))}else{let M=g.props,w=M==null?void 0:M.className,A=typeof w=="function"?(...q)=>Ms(w(...q),h.className):Ms(w,h.className),E=A?{className:A}:{},R=np(g.props,Pa(Ss(h,["ref"])));for(let q in z)q in R&&delete z[q];return y.cloneElement(g,Object.assign({},R,z,p,{ref:s(Iy(g),p.ref)},E))}return y.createElement(f,Object.assign({},Ss(h,["ref"]),f!==y.Fragment&&p,f!==y.Fragment&&z),g)}function Jy(){let a=y.useRef([]),r=y.useCallback(i=>{for(let u of a.current)u!=null&&(typeof u=="function"?u(i):u.current=i)},[]);return(...i)=>{if(!i.every(u=>u==null))return a.current=i,r}}function Wy(...a){return a.every(r=>r==null)?void 0:r=>{for(let i of a)i!=null&&(typeof i=="function"?i(r):i.current=r)}}function np(...a){if(a.length===0)return{};if(a.length===1)return a[0];let r={},i={};for(let u of a)for(let s in u)s.startsWith("on")&&typeof u[s]=="function"?(i[s]!=null||(i[s]=[]),i[s].push(u[s])):r[s]=u[s];if(r.disabled||r["aria-disabled"])for(let u in i)/^(on(?:Click|Pointer|Mouse|Key)(?:Down|Up|Press)?)$/.test(u)&&(i[u]=[s=>{var f;return(f=s==null?void 0:s.preventDefault)==null?void 0:f.call(s)}]);for(let u in i)Object.assign(r,{[u](s,...f){let m=i[u];for(let v of m){if((s instanceof Event||(s==null?void 0:s.nativeEvent)instanceof Event)&&s.defaultPrevented)return;v(s,...f)}}});return r}function Ct(a){var r;return Object.assign(y.forwardRef(a),{displayName:(r=a.displayName)!=null?r:a.name})}function Pa(a){let r=Object.assign({},a);for(let i in r)r[i]===void 0&&delete r[i];return r}function Ss(a,r=[]){let i=Object.assign({},a);for(let u of r)u in i&&delete i[u];return i}function Iy(a){return X.version.split(".")[0]>="19"?a.props.ref:a.ref}let e1="span";var Bo=(a=>(a[a.None=1]="None",a[a.Focusable=2]="Focusable",a[a.Hidden=4]="Hidden",a))(Bo||{});function t1(a,r){var i;let{features:u=1,...s}=a,f={ref:r,"aria-hidden":(u&2)===2?!0:(i=s["aria-hidden"])!=null?i:void 0,hidden:(u&4)===4?!0:void 0,style:{position:"fixed",top:1,left:1,width:1,height:0,padding:0,margin:-1,overflow:"hidden",clip:"rect(0, 0, 0, 0)",whiteSpace:"nowrap",borderWidth:"0",...(u&4)===4&&(u&2)!==2&&{display:"none"}}};return en()({ourProps:f,theirProps:s,slot:{},defaultTag:e1,name:"Hidden"})}let Cs=Ct(t1),ap=Symbol();function n1(a,r=!0){return Object.assign(a,{[ap]:r})}function Sn(...a){let r=y.useRef(a);y.useEffect(()=>{r.current=a},[a]);let i=Ze(u=>{for(let s of r.current)s!=null&&(typeof s=="function"?s(u):s.current=u)});return a.every(u=>u==null||(u==null?void 0:u[ap]))?void 0:i}let Is=y.createContext(null);Is.displayName="DescriptionContext";function lp(){let a=y.useContext(Is);if(a===null){let r=new Error("You used a <Description /> component, but it is not inside a relevant parent.");throw Error.captureStackTrace&&Error.captureStackTrace(r,lp),r}return a}function a1(){let[a,r]=y.useState([]);return[a.length>0?a.join(" "):void 0,y.useMemo(()=>function(i){let u=Ze(f=>(r(m=>[...m,f]),()=>r(m=>{let v=m.slice(),h=v.indexOf(f);return h!==-1&&v.splice(h,1),v}))),s=y.useMemo(()=>({register:u,slot:i.slot,name:i.name,props:i.props,value:i.value}),[u,i.slot,i.name,i.props,i.value]);return X.createElement(Is.Provider,{value:s},i.children)},[r])]}let l1="p";function r1(a,r){let i=y.useId(),u=Fy(),{id:s=`headlessui-description-${i}`,...f}=a,m=lp(),v=Sn(r);zt(()=>m.register(s),[s,m.register]);let h=u||!1,p=y.useMemo(()=>({...m.slot,disabled:h}),[m.slot,h]),g={ref:v,...m.props,id:s};return en()({ourProps:g,theirProps:f,slot:p,defaultTag:l1,name:m.name||"Description"})}let i1=Ct(r1),o1=Object.assign(i1,{});var rp=(a=>(a.Space=" ",a.Enter="Enter",a.Escape="Escape",a.Backspace="Backspace",a.Delete="Delete",a.ArrowLeft="ArrowLeft",a.ArrowUp="ArrowUp",a.ArrowRight="ArrowRight",a.ArrowDown="ArrowDown",a.Home="Home",a.End="End",a.PageUp="PageUp",a.PageDown="PageDown",a.Tab="Tab",a))(rp||{});let u1=y.createContext(()=>{});function c1({value:a,children:r}){return X.createElement(u1.Provider,{value:a},r)}let s1=class extends Map{constructor(r){super(),this.factory=r}get(r){let i=super.get(r);return i===void 0&&(i=this.factory(r),this.set(r,i)),i}};function ip(a,r){let i=a(),u=new Set;return{getSnapshot(){return i},subscribe(s){return u.add(s),()=>u.delete(s)},dispatch(s,...f){let m=r[s].call(i,...f);m&&(i=m,u.forEach(v=>v()))}}}function op(a){return y.useSyncExternalStore(a.subscribe,a.getSnapshot,a.getSnapshot)}let f1=new s1(()=>ip(()=>[],{ADD(a){return this.includes(a)?this:[...this,a]},REMOVE(a){let r=this.indexOf(a);if(r===-1)return this;let i=this.slice();return i.splice(r,1),i}}));function Zl(a,r){let i=f1.get(r),u=y.useId(),s=op(i);if(zt(()=>{if(a)return i.dispatch("ADD",u),()=>i.dispatch("REMOVE",u)},[i,a]),!a)return!1;let f=s.indexOf(u),m=s.length;return f===-1&&(f=m,m+=1),f===m-1}let ks=new Map,Wr=new Map;function rm(a){var r;let i=(r=Wr.get(a))!=null?r:0;return Wr.set(a,i+1),i!==0?()=>im(a):(ks.set(a,{"aria-hidden":a.getAttribute("aria-hidden"),inert:a.inert}),a.setAttribute("aria-hidden","true"),a.inert=!0,()=>im(a))}function im(a){var r;let i=(r=Wr.get(a))!=null?r:1;if(i===1?Wr.delete(a):Wr.set(a,i-1),i!==1)return;let u=ks.get(a);u&&(u["aria-hidden"]===null?a.removeAttribute("aria-hidden"):a.setAttribute("aria-hidden",u["aria-hidden"]),a.inert=u.inert,ks.delete(a))}function d1(a,{allowed:r,disallowed:i}={}){let u=Zl(a,"inert-others");zt(()=>{var s,f;if(!u)return;let m=Oa();for(let h of(s=i==null?void 0:i())!=null?s:[])h&&m.add(rm(h));let v=(f=r==null?void 0:r())!=null?f:[];for(let h of v){if(!h)continue;let p=Jo(h);if(!p)continue;let g=h.parentElement;for(;g&&g!==p.body;){for(let z of g.children)v.some(M=>z.contains(M))||m.add(rm(z));g=g.parentElement}}return m.dispose},[u,r,i])}function h1(a,r,i){let u=Ia(s=>{let f=s.getBoundingClientRect();f.x===0&&f.y===0&&f.width===0&&f.height===0&&i()});y.useEffect(()=>{if(!a)return;let s=r===null?null:r instanceof HTMLElement?r:r.current;if(!s)return;let f=Oa();if(typeof ResizeObserver<"u"){let m=new ResizeObserver(()=>u.current(s));m.observe(s),f.add(()=>m.disconnect())}if(typeof IntersectionObserver<"u"){let m=new IntersectionObserver(()=>u.current(s));m.observe(s),f.add(()=>m.disconnect())}return()=>f.dispose()},[r,u,a])}let Vo=["[contentEditable=true]","[tabindex]","a[href]","area[href]","button:not([disabled])","iframe","input:not([disabled])","select:not([disabled])","textarea:not([disabled])"].map(a=>`${a}:not([tabindex='-1'])`).join(","),m1=["[data-autofocus]"].map(a=>`${a}:not([tabindex='-1'])`).join(",");var Xn=(a=>(a[a.First=1]="First",a[a.Previous=2]="Previous",a[a.Next=4]="Next",a[a.Last=8]="Last",a[a.WrapAround=16]="WrapAround",a[a.NoScroll=32]="NoScroll",a[a.AutoFocus=64]="AutoFocus",a))(Xn||{}),Ns=(a=>(a[a.Error=0]="Error",a[a.Overflow=1]="Overflow",a[a.Success=2]="Success",a[a.Underflow=3]="Underflow",a))(Ns||{}),p1=(a=>(a[a.Previous=-1]="Previous",a[a.Next=1]="Next",a))(p1||{});function v1(a=document.body){return a==null?[]:Array.from(a.querySelectorAll(Vo)).sort((r,i)=>Math.sign((r.tabIndex||Number.MAX_SAFE_INTEGER)-(i.tabIndex||Number.MAX_SAFE_INTEGER)))}function b1(a=document.body){return a==null?[]:Array.from(a.querySelectorAll(m1)).sort((r,i)=>Math.sign((r.tabIndex||Number.MAX_SAFE_INTEGER)-(i.tabIndex||Number.MAX_SAFE_INTEGER)))}var up=(a=>(a[a.Strict=0]="Strict",a[a.Loose=1]="Loose",a))(up||{});function g1(a,r=0){var i;return a===((i=Jo(a))==null?void 0:i.body)?!1:Ea(r,{0(){return a.matches(Vo)},1(){let u=a;for(;u!==null;){if(u.matches(Vo))return!0;u=u.parentElement}return!1}})}var y1=(a=>(a[a.Keyboard=0]="Keyboard",a[a.Mouse=1]="Mouse",a))(y1||{});typeof window<"u"&&typeof document<"u"&&(document.addEventListener("keydown",a=>{a.metaKey||a.altKey||a.ctrlKey||(document.documentElement.dataset.headlessuiFocusVisible="")},!0),document.addEventListener("click",a=>{a.detail===1?delete document.documentElement.dataset.headlessuiFocusVisible:a.detail===0&&(document.documentElement.dataset.headlessuiFocusVisible="")},!0));function Zn(a){a==null||a.focus({preventScroll:!0})}let x1=["textarea","input"].join(",");function S1(a){var r,i;return(i=(r=a==null?void 0:a.matches)==null?void 0:r.call(a,x1))!=null?i:!1}function E1(a,r=i=>i){return a.slice().sort((i,u)=>{let s=r(i),f=r(u);if(s===null||f===null)return 0;let m=s.compareDocumentPosition(f);return m&Node.DOCUMENT_POSITION_FOLLOWING?-1:m&Node.DOCUMENT_POSITION_PRECEDING?1:0})}function Ir(a,r,{sorted:i=!0,relativeTo:u=null,skipElements:s=[]}={}){let f=Array.isArray(a)?a.length>0?a[0].ownerDocument:document:a.ownerDocument,m=Array.isArray(a)?i?E1(a):a:r&64?b1(a):v1(a);s.length>0&&m.length>1&&(m=m.filter(w=>!s.some(A=>A!=null&&"current"in A?(A==null?void 0:A.current)===w:A===w))),u=u??f.activeElement;let v=(()=>{if(r&5)return 1;if(r&10)return-1;throw new Error("Missing Focus.First, Focus.Previous, Focus.Next or Focus.Last")})(),h=(()=>{if(r&1)return 0;if(r&2)return Math.max(0,m.indexOf(u))-1;if(r&4)return Math.max(0,m.indexOf(u))+1;if(r&8)return m.length-1;throw new Error("Missing Focus.First, Focus.Previous, Focus.Next or Focus.Last")})(),p=r&32?{preventScroll:!0}:{},g=0,z=m.length,M;do{if(g>=z||g+z<=0)return 0;let w=h+g;if(r&16)w=(w+z)%z;else{if(w<0)return 3;if(w>=z)return 1}M=m[w],M==null||M.focus(p),g+=v}while(M!==f.activeElement);return r&6&&S1(M)&&M.select(),2}function cp(){return/iPhone/gi.test(window.navigator.platform)||/Mac/gi.test(window.navigator.platform)&&window.navigator.maxTouchPoints>0}function O1(){return/Android/gi.test(window.navigator.userAgent)}function A1(){return cp()||O1()}function Zr(a,r,i,u){let s=Ia(i);y.useEffect(()=>{if(!a)return;function f(m){s.current(m)}return document.addEventListener(r,f,u),()=>document.removeEventListener(r,f,u)},[a,r,u])}function sp(a,r,i,u){let s=Ia(i);y.useEffect(()=>{if(!a)return;function f(m){s.current(m)}return window.addEventListener(r,f,u),()=>window.removeEventListener(r,f,u)},[a,r,u])}const om=30;function T1(a,r,i){let u=Zl(a,"outside-click"),s=Ia(i),f=y.useCallback(function(h,p){if(h.defaultPrevented)return;let g=p(h);if(g===null||!g.getRootNode().contains(g)||!g.isConnected)return;let z=function M(w){return typeof w=="function"?M(w()):Array.isArray(w)||w instanceof Set?w:[w]}(r);for(let M of z)if(M!==null&&(M.contains(g)||h.composed&&h.composedPath().includes(M)))return;return!g1(g,up.Loose)&&g.tabIndex!==-1&&h.preventDefault(),s.current(h,g)},[s,r]),m=y.useRef(null);Zr(u,"pointerdown",h=>{var p,g;m.current=((g=(p=h.composedPath)==null?void 0:p.call(h))==null?void 0:g[0])||h.target},!0),Zr(u,"mousedown",h=>{var p,g;m.current=((g=(p=h.composedPath)==null?void 0:p.call(h))==null?void 0:g[0])||h.target},!0),Zr(u,"click",h=>{A1()||m.current&&(f(h,()=>m.current),m.current=null)},!0);let v=y.useRef({x:0,y:0});Zr(u,"touchstart",h=>{v.current.x=h.touches[0].clientX,v.current.y=h.touches[0].clientY},!0),Zr(u,"touchend",h=>{let p={x:h.changedTouches[0].clientX,y:h.changedTouches[0].clientY};if(!(Math.abs(p.x-v.current.x)>=om||Math.abs(p.y-v.current.y)>=om))return f(h,()=>h.target instanceof HTMLElement?h.target:null)},!0),sp(u,"blur",h=>f(h,()=>window.document.activeElement instanceof HTMLIFrameElement?window.document.activeElement:null),!0)}function ri(...a){return y.useMemo(()=>Jo(...a),[...a])}function fp(a,r,i,u){let s=Ia(i);y.useEffect(()=>{a=a??window;function f(m){s.current(m)}return a.addEventListener(r,f,u),()=>a.removeEventListener(r,f,u)},[a,r,u])}function _1(){let a;return{before({doc:r}){var i;let u=r.documentElement,s=(i=r.defaultView)!=null?i:window;a=Math.max(0,s.innerWidth-u.clientWidth)},after({doc:r,d:i}){let u=r.documentElement,s=Math.max(0,u.clientWidth-u.offsetWidth),f=Math.max(0,a-s);i.style(u,"paddingRight",`${f}px`)}}}function z1(){return cp()?{before({doc:a,d:r,meta:i}){function u(s){return i.containers.flatMap(f=>f()).some(f=>f.contains(s))}r.microTask(()=>{var s;if(window.getComputedStyle(a.documentElement).scrollBehavior!=="auto"){let v=Oa();v.style(a.documentElement,"scrollBehavior","auto"),r.add(()=>r.microTask(()=>v.dispose()))}let f=(s=window.scrollY)!=null?s:window.pageYOffset,m=null;r.addEventListener(a,"click",v=>{if(v.target instanceof HTMLElement)try{let h=v.target.closest("a");if(!h)return;let{hash:p}=new URL(h.href),g=a.querySelector(p);g&&!u(g)&&(m=g)}catch{}},!0),r.addEventListener(a,"touchstart",v=>{if(v.target instanceof HTMLElement)if(u(v.target)){let h=v.target;for(;h.parentElement&&u(h.parentElement);)h=h.parentElement;r.style(h,"overscrollBehavior","contain")}else r.style(v.target,"touchAction","none")}),r.addEventListener(a,"touchmove",v=>{if(v.target instanceof HTMLElement){if(v.target.tagName==="INPUT")return;if(u(v.target)){let h=v.target;for(;h.parentElement&&h.dataset.headlessuiPortal!==""&&!(h.scrollHeight>h.clientHeight||h.scrollWidth>h.clientWidth);)h=h.parentElement;h.dataset.headlessuiPortal===""&&v.preventDefault()}else v.preventDefault()}},{passive:!1}),r.add(()=>{var v;let h=(v=window.scrollY)!=null?v:window.pageYOffset;f!==h&&window.scrollTo(0,f),m&&m.isConnected&&(m.scrollIntoView({block:"nearest"}),m=null)})})}}:{}}function D1(){return{before({doc:a,d:r}){r.style(a.documentElement,"overflow","hidden")}}}function w1(a){let r={};for(let i of a)Object.assign(r,i(r));return r}let Ka=ip(()=>new Map,{PUSH(a,r){var i;let u=(i=this.get(a))!=null?i:{doc:a,count:0,d:Oa(),meta:new Set};return u.count++,u.meta.add(r),this.set(a,u),this},POP(a,r){let i=this.get(a);return i&&(i.count--,i.meta.delete(r)),this},SCROLL_PREVENT({doc:a,d:r,meta:i}){let u={doc:a,d:r,meta:w1(i)},s=[z1(),_1(),D1()];s.forEach(({before:f})=>f==null?void 0:f(u)),s.forEach(({after:f})=>f==null?void 0:f(u))},SCROLL_ALLOW({d:a}){a.dispose()},TEARDOWN({doc:a}){this.delete(a)}});Ka.subscribe(()=>{let a=Ka.getSnapshot(),r=new Map;for(let[i]of a)r.set(i,i.documentElement.style.overflow);for(let i of a.values()){let u=r.get(i.doc)==="hidden",s=i.count!==0;(s&&!u||!s&&u)&&Ka.dispatch(i.count>0?"SCROLL_PREVENT":"SCROLL_ALLOW",i),i.count===0&&Ka.dispatch("TEARDOWN",i)}});function R1(a,r,i=()=>({containers:[]})){let u=op(Ka),s=r?u.get(r):void 0,f=s?s.count>0:!1;return zt(()=>{if(!(!r||!a))return Ka.dispatch("PUSH",r,i),()=>Ka.dispatch("POP",r,i)},[a,r]),f}function M1(a,r,i=()=>[document.body]){let u=Zl(a,"scroll-lock");R1(u,r,s=>{var f;return{containers:[...(f=s.containers)!=null?f:[],i]}})}function C1(a=0){let[r,i]=y.useState(a),u=y.useCallback(h=>i(h),[r]),s=y.useCallback(h=>i(p=>p|h),[r]),f=y.useCallback(h=>(r&h)===h,[r]),m=y.useCallback(h=>i(p=>p&~h),[i]),v=y.useCallback(h=>i(p=>p^h),[i]);return{flags:r,setFlag:u,addFlag:s,hasFlag:f,removeFlag:m,toggleFlag:v}}var k1={},um,cm;typeof process<"u"&&typeof globalThis<"u"&&typeof Element<"u"&&((um=process==null?void 0:k1)==null?void 0:um.NODE_ENV)==="test"&&typeof((cm=Element==null?void 0:Element.prototype)==null?void 0:cm.getAnimations)>"u"&&(Element.prototype.getAnimations=function(){return console.warn(["Headless UI has polyfilled `Element.prototype.getAnimations` for your tests.","Please install a proper polyfill e.g. `jsdom-testing-mocks`, to silence these warnings.","","Example usage:","```js","import { mockAnimationsApi } from 'jsdom-testing-mocks'","mockAnimationsApi()","```"].join(`
`)),[]});var N1=(a=>(a[a.None=0]="None",a[a.Closed=1]="Closed",a[a.Enter=2]="Enter",a[a.Leave=4]="Leave",a))(N1||{});function U1(a){let r={};for(let i in a)a[i]===!0&&(r[`data-${i}`]="");return r}function q1(a,r,i,u){let[s,f]=y.useState(i),{hasFlag:m,addFlag:v,removeFlag:h}=C1(a&&s?3:0),p=y.useRef(!1),g=y.useRef(!1),z=Ws();return zt(()=>{var M;if(a){if(i&&f(!0),!r){i&&v(3);return}return(M=u==null?void 0:u.start)==null||M.call(u,i),H1(r,{inFlight:p,prepare(){g.current?g.current=!1:g.current=p.current,p.current=!0,!g.current&&(i?(v(3),h(4)):(v(4),h(2)))},run(){g.current?i?(h(3),v(4)):(h(4),v(3)):i?h(1):v(1)},done(){var w;g.current&&typeof r.getAnimations=="function"&&r.getAnimations().length>0||(p.current=!1,h(7),i||f(!1),(w=u==null?void 0:u.end)==null||w.call(u,i))}})}},[a,i,r,z]),a?[s,{closed:m(1),enter:m(2),leave:m(4),transition:m(2)||m(4)}]:[i,{closed:void 0,enter:void 0,leave:void 0,transition:void 0}]}function H1(a,{prepare:r,run:i,done:u,inFlight:s}){let f=Oa();return B1(a,{prepare:r,inFlight:s}),f.nextFrame(()=>{i(),f.requestAnimationFrame(()=>{f.add(L1(a,u))})}),f.dispose}function L1(a,r){var i,u;let s=Oa();if(!a)return s.dispose;let f=!1;s.add(()=>{f=!0});let m=(u=(i=a.getAnimations)==null?void 0:i.call(a).filter(v=>v instanceof CSSTransition))!=null?u:[];return m.length===0?(r(),s.dispose):(Promise.allSettled(m.map(v=>v.finished)).then(()=>{f||r()}),s.dispose)}function B1(a,{inFlight:r,prepare:i}){if(r!=null&&r.current){i();return}let u=a.style.transition;a.style.transition="none",i(),a.offsetHeight,a.style.transition=u}function ef(a,r){let i=y.useRef([]),u=Ze(a);y.useEffect(()=>{let s=[...i.current];for(let[f,m]of r.entries())if(i.current[f]!==m){let v=u(r,s);return i.current=r,v}},[u,...r])}let Io=y.createContext(null);Io.displayName="OpenClosedContext";var sn=(a=>(a[a.Open=1]="Open",a[a.Closed=2]="Closed",a[a.Closing=4]="Closing",a[a.Opening=8]="Opening",a))(sn||{});function eu(){return y.useContext(Io)}function V1({value:a,children:r}){return X.createElement(Io.Provider,{value:a},r)}function j1({children:a}){return X.createElement(Io.Provider,{value:null},a)}function Y1(a){function r(){document.readyState!=="loading"&&(a(),document.removeEventListener("DOMContentLoaded",r))}typeof window<"u"&&typeof document<"u"&&(document.addEventListener("DOMContentLoaded",r),r())}let ya=[];Y1(()=>{function a(r){if(!(r.target instanceof HTMLElement)||r.target===document.body||ya[0]===r.target)return;let i=r.target;i=i.closest(Vo),ya.unshift(i??r.target),ya=ya.filter(u=>u!=null&&u.isConnected),ya.splice(10)}window.addEventListener("click",a,{capture:!0}),window.addEventListener("mousedown",a,{capture:!0}),window.addEventListener("focus",a,{capture:!0}),document.body.addEventListener("click",a,{capture:!0}),document.body.addEventListener("mousedown",a,{capture:!0}),document.body.addEventListener("focus",a,{capture:!0})});function dp(a){let r=Ze(a),i=y.useRef(!1);y.useEffect(()=>(i.current=!1,()=>{i.current=!0,Wo(()=>{i.current&&r()})}),[r])}function X1(){let a=typeof document>"u";return"useSyncExternalStore"in F0?(r=>r.useSyncExternalStore)(F0)(()=>()=>{},()=>!1,()=>!a):!1}function ii(){let a=X1(),[r,i]=y.useState(Wa.isHandoffComplete);return r&&Wa.isHandoffComplete===!1&&i(!1),y.useEffect(()=>{r!==!0&&i(!0)},[r]),y.useEffect(()=>Wa.handoff(),[]),a?!1:r}let hp=y.createContext(!1);function G1(){return y.useContext(hp)}function sm(a){return X.createElement(hp.Provider,{value:a.force},a.children)}function Q1(a){let r=G1(),i=y.useContext(pp),u=ri(a),[s,f]=y.useState(()=>{var m;if(!r&&i!==null)return(m=i.current)!=null?m:null;if(Wa.isServer)return null;let v=u==null?void 0:u.getElementById("headlessui-portal-root");if(v)return v;if(u===null)return null;let h=u.createElement("div");return h.setAttribute("id","headlessui-portal-root"),u.body.appendChild(h)});return y.useEffect(()=>{s!==null&&(u!=null&&u.body.contains(s)||u==null||u.body.appendChild(s))},[s,u]),y.useEffect(()=>{r||i!==null&&f(i.current)},[i,f,r]),s}let mp=y.Fragment,Z1=Ct(function(a,r){let i=a,u=y.useRef(null),s=Sn(n1(z=>{u.current=z}),r),f=ri(u),m=Q1(u),[v]=y.useState(()=>{var z;return Wa.isServer?null:(z=f==null?void 0:f.createElement("div"))!=null?z:null}),h=y.useContext(Us),p=ii();zt(()=>{!m||!v||m.contains(v)||(v.setAttribute("data-headlessui-portal",""),m.appendChild(v))},[m,v]),zt(()=>{if(v&&h)return h.register(v)},[h,v]),dp(()=>{var z;!m||!v||(v instanceof Node&&m.contains(v)&&m.removeChild(v),m.childNodes.length<=0&&((z=m.parentElement)==null||z.removeChild(m)))});let g=en();return p?!m||!v?null:tp.createPortal(g({ourProps:{ref:s},theirProps:i,slot:{},defaultTag:mp,name:"Portal"}),v):null});function $1(a,r){let i=Sn(r),{enabled:u=!0,...s}=a,f=en();return u?X.createElement(Z1,{...s,ref:i}):f({ourProps:{ref:i},theirProps:s,slot:{},defaultTag:mp,name:"Portal"})}let P1=y.Fragment,pp=y.createContext(null);function F1(a,r){let{target:i,...u}=a,s={ref:Sn(r)},f=en();return X.createElement(pp.Provider,{value:i},f({ourProps:s,theirProps:u,defaultTag:P1,name:"Popover.Group"}))}let Us=y.createContext(null);function K1(){let a=y.useContext(Us),r=y.useRef([]),i=Ze(f=>(r.current.push(f),a&&a.register(f),()=>u(f))),u=Ze(f=>{let m=r.current.indexOf(f);m!==-1&&r.current.splice(m,1),a&&a.unregister(f)}),s=y.useMemo(()=>({register:i,unregister:u,portals:r}),[i,u,r]);return[r,y.useMemo(()=>function({children:f}){return X.createElement(Us.Provider,{value:s},f)},[s])]}let J1=Ct($1),vp=Ct(F1),W1=Object.assign(J1,{Group:vp});function I1(a,r=typeof document<"u"?document.defaultView:null,i){let u=Zl(a,"escape");fp(r,"keydown",s=>{u&&(s.defaultPrevented||s.key===rp.Escape&&i(s))})}function e2(){var a;let[r]=y.useState(()=>typeof window<"u"&&typeof window.matchMedia=="function"?window.matchMedia("(pointer: coarse)"):null),[i,u]=y.useState((a=r==null?void 0:r.matches)!=null?a:!1);return zt(()=>{if(!r)return;function s(f){u(f.matches)}return r.addEventListener("change",s),()=>r.removeEventListener("change",s)},[r]),i}function t2({defaultContainers:a=[],portals:r,mainTreeNode:i}={}){let u=ri(i),s=Ze(()=>{var f,m;let v=[];for(let h of a)h!==null&&(h instanceof HTMLElement?v.push(h):"current"in h&&h.current instanceof HTMLElement&&v.push(h.current));if(r!=null&&r.current)for(let h of r.current)v.push(h);for(let h of(f=u==null?void 0:u.querySelectorAll("html > *, body > *"))!=null?f:[])h!==document.body&&h!==document.head&&h instanceof HTMLElement&&h.id!=="headlessui-portal-root"&&(i&&(h.contains(i)||h.contains((m=i==null?void 0:i.getRootNode())==null?void 0:m.host))||v.some(p=>h.contains(p))||v.push(h));return v});return{resolveContainers:s,contains:Ze(f=>s().some(m=>m.contains(f)))}}let bp=y.createContext(null);function fm({children:a,node:r}){let[i,u]=y.useState(null),s=gp(r??i);return X.createElement(bp.Provider,{value:s},a,s===null&&X.createElement(Cs,{features:Bo.Hidden,ref:f=>{var m,v;if(f){for(let h of(v=(m=Jo(f))==null?void 0:m.querySelectorAll("html > *, body > *"))!=null?v:[])if(h!==document.body&&h!==document.head&&h instanceof HTMLElement&&h!=null&&h.contains(f)){u(h);break}}}}))}function gp(a=null){var r;return(r=y.useContext(bp))!=null?r:a}function tf(){let a=y.useRef(!1);return zt(()=>(a.current=!0,()=>{a.current=!1}),[]),a}var Kr=(a=>(a[a.Forwards=0]="Forwards",a[a.Backwards=1]="Backwards",a))(Kr||{});function n2(){let a=y.useRef(0);return sp(!0,"keydown",r=>{r.key==="Tab"&&(a.current=r.shiftKey?1:0)},!0),a}function yp(a){if(!a)return new Set;if(typeof a=="function")return new Set(a());let r=new Set;for(let i of a.current)i.current instanceof HTMLElement&&r.add(i.current);return r}let a2="div";var Fa=(a=>(a[a.None=0]="None",a[a.InitialFocus=1]="InitialFocus",a[a.TabLock=2]="TabLock",a[a.FocusLock=4]="FocusLock",a[a.RestoreFocus=8]="RestoreFocus",a[a.AutoFocus=16]="AutoFocus",a))(Fa||{});function l2(a,r){let i=y.useRef(null),u=Sn(i,r),{initialFocus:s,initialFocusFallback:f,containers:m,features:v=15,...h}=a;ii()||(v=0);let p=ri(i);u2(v,{ownerDocument:p});let g=c2(v,{ownerDocument:p,container:i,initialFocus:s,initialFocusFallback:f});s2(v,{ownerDocument:p,container:i,containers:m,previousActiveElement:g});let z=n2(),M=Ze(N=>{let V=i.current;V&&(F=>F())(()=>{Ea(z.current,{[Kr.Forwards]:()=>{Ir(V,Xn.First,{skipElements:[N.relatedTarget,f]})},[Kr.Backwards]:()=>{Ir(V,Xn.Last,{skipElements:[N.relatedTarget,f]})}})})}),w=Zl(!!(v&2),"focus-trap#tab-lock"),A=Ws(),E=y.useRef(!1),R={ref:u,onKeyDown(N){N.key=="Tab"&&(E.current=!0,A.requestAnimationFrame(()=>{E.current=!1}))},onBlur(N){if(!(v&4))return;let V=yp(m);i.current instanceof HTMLElement&&V.add(i.current);let F=N.relatedTarget;F instanceof HTMLElement&&F.dataset.headlessuiFocusGuard!=="true"&&(xp(V,F)||(E.current?Ir(i.current,Ea(z.current,{[Kr.Forwards]:()=>Xn.Next,[Kr.Backwards]:()=>Xn.Previous})|Xn.WrapAround,{relativeTo:N.target}):N.target instanceof HTMLElement&&Zn(N.target)))}},q=en();return X.createElement(X.Fragment,null,w&&X.createElement(Cs,{as:"button",type:"button","data-headlessui-focus-guard":!0,onFocus:M,features:Bo.Focusable}),q({ourProps:R,theirProps:h,defaultTag:a2,name:"FocusTrap"}),w&&X.createElement(Cs,{as:"button",type:"button","data-headlessui-focus-guard":!0,onFocus:M,features:Bo.Focusable}))}let r2=Ct(l2),i2=Object.assign(r2,{features:Fa});function o2(a=!0){let r=y.useRef(ya.slice());return ef(([i],[u])=>{u===!0&&i===!1&&Wo(()=>{r.current.splice(0)}),u===!1&&i===!0&&(r.current=ya.slice())},[a,ya,r]),Ze(()=>{var i;return(i=r.current.find(u=>u!=null&&u.isConnected))!=null?i:null})}function u2(a,{ownerDocument:r}){let i=!!(a&8),u=o2(i);ef(()=>{i||(r==null?void 0:r.activeElement)===(r==null?void 0:r.body)&&Zn(u())},[i]),dp(()=>{i&&Zn(u())})}function c2(a,{ownerDocument:r,container:i,initialFocus:u,initialFocusFallback:s}){let f=y.useRef(null),m=Zl(!!(a&1),"focus-trap#initial-focus"),v=tf();return ef(()=>{if(a===0)return;if(!m){s!=null&&s.current&&Zn(s.current);return}let h=i.current;h&&Wo(()=>{if(!v.current)return;let p=r==null?void 0:r.activeElement;if(u!=null&&u.current){if((u==null?void 0:u.current)===p){f.current=p;return}}else if(h.contains(p)){f.current=p;return}if(u!=null&&u.current)Zn(u.current);else{if(a&16){if(Ir(h,Xn.First|Xn.AutoFocus)!==Ns.Error)return}else if(Ir(h,Xn.First)!==Ns.Error)return;if(s!=null&&s.current&&(Zn(s.current),(r==null?void 0:r.activeElement)===s.current))return;console.warn("There are no focusable elements inside the <FocusTrap />")}f.current=r==null?void 0:r.activeElement})},[s,m,a]),f}function s2(a,{ownerDocument:r,container:i,containers:u,previousActiveElement:s}){let f=tf(),m=!!(a&4);fp(r==null?void 0:r.defaultView,"focus",v=>{if(!m||!f.current)return;let h=yp(u);i.current instanceof HTMLElement&&h.add(i.current);let p=s.current;if(!p)return;let g=v.target;g&&g instanceof HTMLElement?xp(h,g)?(s.current=g,Zn(g)):(v.preventDefault(),v.stopPropagation(),Zn(p)):Zn(s.current)},!0)}function xp(a,r){for(let i of a)if(i.contains(r))return!0;return!1}function Sp(a){var r;return!!(a.enter||a.enterFrom||a.enterTo||a.leave||a.leaveFrom||a.leaveTo)||((r=a.as)!=null?r:Op)!==y.Fragment||X.Children.count(a.children)===1}let tu=y.createContext(null);tu.displayName="TransitionContext";var f2=(a=>(a.Visible="visible",a.Hidden="hidden",a))(f2||{});function d2(){let a=y.useContext(tu);if(a===null)throw new Error("A <Transition.Child /> is used but it is missing a parent <Transition /> or <Transition.Root />.");return a}function h2(){let a=y.useContext(nu);if(a===null)throw new Error("A <Transition.Child /> is used but it is missing a parent <Transition /> or <Transition.Root />.");return a}let nu=y.createContext(null);nu.displayName="NestingContext";function au(a){return"children"in a?au(a.children):a.current.filter(({el:r})=>r.current!==null).filter(({state:r})=>r==="visible").length>0}function Ep(a,r){let i=Ia(a),u=y.useRef([]),s=tf(),f=Ws(),m=Ze((w,A=Sa.Hidden)=>{let E=u.current.findIndex(({el:R})=>R===w);E!==-1&&(Ea(A,{[Sa.Unmount](){u.current.splice(E,1)},[Sa.Hidden](){u.current[E].state="hidden"}}),f.microTask(()=>{var R;!au(u)&&s.current&&((R=i.current)==null||R.call(i))}))}),v=Ze(w=>{let A=u.current.find(({el:E})=>E===w);return A?A.state!=="visible"&&(A.state="visible"):u.current.push({el:w,state:"visible"}),()=>m(w,Sa.Unmount)}),h=y.useRef([]),p=y.useRef(Promise.resolve()),g=y.useRef({enter:[],leave:[]}),z=Ze((w,A,E)=>{h.current.splice(0),r&&(r.chains.current[A]=r.chains.current[A].filter(([R])=>R!==w)),r==null||r.chains.current[A].push([w,new Promise(R=>{h.current.push(R)})]),r==null||r.chains.current[A].push([w,new Promise(R=>{Promise.all(g.current[A].map(([q,N])=>N)).then(()=>R())})]),A==="enter"?p.current=p.current.then(()=>r==null?void 0:r.wait.current).then(()=>E(A)):E(A)}),M=Ze((w,A,E)=>{Promise.all(g.current[A].splice(0).map(([R,q])=>q)).then(()=>{var R;(R=h.current.shift())==null||R()}).then(()=>E(A))});return y.useMemo(()=>({children:u,register:v,unregister:m,onStart:z,onStop:M,wait:p,chains:g}),[v,m,u,z,M,g,p])}let Op=y.Fragment,Ap=Lo.RenderStrategy;function m2(a,r){var i,u;let{transition:s=!0,beforeEnter:f,afterEnter:m,beforeLeave:v,afterLeave:h,enter:p,enterFrom:g,enterTo:z,entered:M,leave:w,leaveFrom:A,leaveTo:E,...R}=a,[q,N]=y.useState(null),V=y.useRef(null),F=Sp(a),$=Sn(...F?[V,r,N]:r===null?[]:[r]),me=(i=R.unmount)==null||i?Sa.Unmount:Sa.Hidden,{show:pe,appear:ve,initial:G}=d2(),[W,ce]=y.useState(pe?"visible":"hidden"),oe=h2(),{register:re,unregister:ge}=oe;zt(()=>re(V),[re,V]),zt(()=>{if(me===Sa.Hidden&&V.current){if(pe&&W!=="visible"){ce("visible");return}return Ea(W,{hidden:()=>ge(V),visible:()=>re(V)})}},[W,V,re,ge,pe,me]);let Te=ii();zt(()=>{if(F&&Te&&W==="visible"&&V.current===null)throw new Error("Did you forget to passthrough the `ref` to the actual DOM node?")},[V,W,Te,F]);let Be=G&&!ve,tt=ve&&pe&&G,Ve=y.useRef(!1),Je=Ep(()=>{Ve.current||(ce("hidden"),ge(V))},oe),T=Ze($e=>{Ve.current=!0;let Se=$e?"enter":"leave";Je.onStart(V,Se,Me=>{Me==="enter"?f==null||f():Me==="leave"&&(v==null||v())})}),Y=Ze($e=>{let Se=$e?"enter":"leave";Ve.current=!1,Je.onStop(V,Se,Me=>{Me==="enter"?m==null||m():Me==="leave"&&(h==null||h())}),Se==="leave"&&!au(Je)&&(ce("hidden"),ge(V))});y.useEffect(()=>{F&&s||(T(pe),Y(pe))},[pe,F,s]);let ue=!(!s||!F||!Te||Be),[,ee]=q1(ue,q,pe,{start:T,end:Y}),ne=Pa({ref:$,className:((u=Ms(R.className,tt&&p,tt&&g,ee.enter&&p,ee.enter&&ee.closed&&g,ee.enter&&!ee.closed&&z,ee.leave&&w,ee.leave&&!ee.closed&&A,ee.leave&&ee.closed&&E,!ee.transition&&pe&&M))==null?void 0:u.trim())||void 0,...U1(ee)}),ye=0;W==="visible"&&(ye|=sn.Open),W==="hidden"&&(ye|=sn.Closed),ee.enter&&(ye|=sn.Opening),ee.leave&&(ye|=sn.Closing);let de=en();return X.createElement(nu.Provider,{value:Je},X.createElement(V1,{value:ye},de({ourProps:ne,theirProps:R,defaultTag:Op,features:Ap,visible:W==="visible",name:"Transition.Child"})))}function p2(a,r){let{show:i,appear:u=!1,unmount:s=!0,...f}=a,m=y.useRef(null),v=Sp(a),h=Sn(...v?[m,r]:r===null?[]:[r]);ii();let p=eu();if(i===void 0&&p!==null&&(i=(p&sn.Open)===sn.Open),i===void 0)throw new Error("A <Transition /> is used but it is missing a `show={true | false}` prop.");let[g,z]=y.useState(i?"visible":"hidden"),M=Ep(()=>{i||z("hidden")}),[w,A]=y.useState(!0),E=y.useRef([i]);zt(()=>{w!==!1&&E.current[E.current.length-1]!==i&&(E.current.push(i),A(!1))},[E,i]);let R=y.useMemo(()=>({show:i,appear:u,initial:w}),[i,u,w]);zt(()=>{i?z("visible"):!au(M)&&m.current!==null&&z("hidden")},[i,M]);let q={unmount:s},N=Ze(()=>{var $;w&&A(!1),($=a.beforeEnter)==null||$.call(a)}),V=Ze(()=>{var $;w&&A(!1),($=a.beforeLeave)==null||$.call(a)}),F=en();return X.createElement(nu.Provider,{value:M},X.createElement(tu.Provider,{value:R},F({ourProps:{...q,as:y.Fragment,children:X.createElement(Tp,{ref:h,...q,...f,beforeEnter:N,beforeLeave:V})},theirProps:{},defaultTag:y.Fragment,features:Ap,visible:g==="visible",name:"Transition"})))}function v2(a,r){let i=y.useContext(tu)!==null,u=eu()!==null;return X.createElement(X.Fragment,null,!i&&u?X.createElement(qs,{ref:r,...a}):X.createElement(Tp,{ref:r,...a}))}let qs=Ct(p2),Tp=Ct(m2),nf=Ct(v2),b2=Object.assign(qs,{Child:nf,Root:qs});var g2=(a=>(a[a.Open=0]="Open",a[a.Closed=1]="Closed",a))(g2||{}),y2=(a=>(a[a.SetTitleId=0]="SetTitleId",a))(y2||{});let x2={0(a,r){return a.titleId===r.id?a:{...a,titleId:r.id}}},af=y.createContext(null);af.displayName="DialogContext";function lu(a){let r=y.useContext(af);if(r===null){let i=new Error(`<${a} /> is missing a parent <Dialog /> component.`);throw Error.captureStackTrace&&Error.captureStackTrace(i,lu),i}return r}function S2(a,r){return Ea(r.type,x2,a,r)}let dm=Ct(function(a,r){let i=y.useId(),{id:u=`headlessui-dialog-${i}`,open:s,onClose:f,initialFocus:m,role:v="dialog",autoFocus:h=!0,__demoMode:p=!1,unmount:g=!1,...z}=a,M=y.useRef(!1);v=function(){return v==="dialog"||v==="alertdialog"?v:(M.current||(M.current=!0,console.warn(`Invalid role [${v}] passed to <Dialog />. Only \`dialog\` and and \`alertdialog\` are supported. Using \`dialog\` instead.`)),"dialog")}();let w=eu();s===void 0&&w!==null&&(s=(w&sn.Open)===sn.Open);let A=y.useRef(null),E=Sn(A,r),R=ri(A),q=s?0:1,[N,V]=y.useReducer(S2,{titleId:null,descriptionId:null,panelRef:y.createRef()}),F=Ze(()=>f(!1)),$=Ze(Y=>V({type:0,id:Y})),me=ii()?q===0:!1,[pe,ve]=K1(),G={get current(){var Y;return(Y=N.panelRef.current)!=null?Y:A.current}},W=gp(),{resolveContainers:ce}=t2({mainTreeNode:W,portals:pe,defaultContainers:[G]}),oe=w!==null?(w&sn.Closing)===sn.Closing:!1;d1(p||oe?!1:me,{allowed:Ze(()=>{var Y,ue;return[(ue=(Y=A.current)==null?void 0:Y.closest("[data-headlessui-portal]"))!=null?ue:null]}),disallowed:Ze(()=>{var Y;return[(Y=W==null?void 0:W.closest("body > *:not(#headlessui-portal-root)"))!=null?Y:null]})}),T1(me,ce,Y=>{Y.preventDefault(),F()}),I1(me,R==null?void 0:R.defaultView,Y=>{Y.preventDefault(),Y.stopPropagation(),document.activeElement&&"blur"in document.activeElement&&typeof document.activeElement.blur=="function"&&document.activeElement.blur(),F()}),M1(p||oe?!1:me,R,ce),h1(me,A,F);let[re,ge]=a1(),Te=y.useMemo(()=>[{dialogState:q,close:F,setTitleId:$,unmount:g},N],[q,N,F,$,g]),Be=y.useMemo(()=>({open:q===0}),[q]),tt={ref:E,id:u,role:v,tabIndex:-1,"aria-modal":p?void 0:q===0?!0:void 0,"aria-labelledby":N.titleId,"aria-describedby":re,unmount:g},Ve=!e2(),Je=Fa.None;me&&!p&&(Je|=Fa.RestoreFocus,Je|=Fa.TabLock,h&&(Je|=Fa.AutoFocus),Ve&&(Je|=Fa.InitialFocus));let T=en();return X.createElement(j1,null,X.createElement(sm,{force:!0},X.createElement(W1,null,X.createElement(af.Provider,{value:Te},X.createElement(vp,{target:A},X.createElement(sm,{force:!1},X.createElement(ge,{slot:Be},X.createElement(ve,null,X.createElement(i2,{initialFocus:m,initialFocusFallback:A,containers:ce,features:Je},X.createElement(c1,{value:F},T({ourProps:tt,theirProps:z,slot:Be,defaultTag:E2,features:O2,visible:q===0,name:"Dialog"})))))))))))}),E2="div",O2=Lo.RenderStrategy|Lo.Static;function A2(a,r){let{transition:i=!1,open:u,...s}=a,f=eu(),m=a.hasOwnProperty("open")||f!==null,v=a.hasOwnProperty("onClose");if(!m&&!v)throw new Error("You have to provide an `open` and an `onClose` prop to the `Dialog` component.");if(!m)throw new Error("You provided an `onClose` prop to the `Dialog`, but forgot an `open` prop.");if(!v)throw new Error("You provided an `open` prop to the `Dialog`, but forgot an `onClose` prop.");if(!f&&typeof a.open!="boolean")throw new Error(`You provided an \`open\` prop to the \`Dialog\`, but the value is not a boolean. Received: ${a.open}`);if(typeof a.onClose!="function")throw new Error(`You provided an \`onClose\` prop to the \`Dialog\`, but the value is not a function. Received: ${a.onClose}`);return(u!==void 0||i)&&!s.static?X.createElement(fm,null,X.createElement(b2,{show:u,transition:i,unmount:s.unmount},X.createElement(dm,{ref:r,...s}))):X.createElement(fm,null,X.createElement(dm,{ref:r,open:u,...s}))}let T2="div";function _2(a,r){let i=y.useId(),{id:u=`headlessui-dialog-panel-${i}`,transition:s=!1,...f}=a,[{dialogState:m,unmount:v},h]=lu("Dialog.Panel"),p=Sn(r,h.panelRef),g=y.useMemo(()=>({open:m===0}),[m]),z=Ze(R=>{R.stopPropagation()}),M={ref:p,id:u,onClick:z},w=s?nf:y.Fragment,A=s?{unmount:v}:{},E=en();return X.createElement(w,{...A},E({ourProps:M,theirProps:f,slot:g,defaultTag:T2,name:"Dialog.Panel"}))}let z2="div";function D2(a,r){let{transition:i=!1,...u}=a,[{dialogState:s,unmount:f}]=lu("Dialog.Backdrop"),m=y.useMemo(()=>({open:s===0}),[s]),v={ref:r,"aria-hidden":!0},h=i?nf:y.Fragment,p=i?{unmount:f}:{},g=en();return X.createElement(h,{...p},g({ourProps:v,theirProps:u,slot:m,defaultTag:z2,name:"Dialog.Backdrop"}))}let w2="h2";function R2(a,r){let i=y.useId(),{id:u=`headlessui-dialog-title-${i}`,...s}=a,[{dialogState:f,setTitleId:m}]=lu("Dialog.Title"),v=Sn(r);y.useEffect(()=>(m(u),()=>m(null)),[u,m]);let h=y.useMemo(()=>({open:f===0}),[f]),p={ref:v,id:u};return en()({ourProps:p,theirProps:s,slot:h,defaultTag:w2,name:"Dialog.Title"})}let M2=Ct(A2),C2=Ct(_2);Ct(D2);let k2=Ct(R2),nx=Object.assign(M2,{Panel:C2,Title:k2,Description:o1});function hm(a,r){var i=Object.keys(a);if(Object.getOwnPropertySymbols){var u=Object.getOwnPropertySymbols(a);r&&(u=u.filter(function(s){return Object.getOwnPropertyDescriptor(a,s).enumerable})),i.push.apply(i,u)}return i}function yn(a){for(var r=1;r<arguments.length;r++){var i=arguments[r]!=null?arguments[r]:{};r%2?hm(Object(i),!0).forEach(function(u){Yl(a,u,i[u])}):Object.getOwnPropertyDescriptors?Object.defineProperties(a,Object.getOwnPropertyDescriptors(i)):hm(Object(i)).forEach(function(u){Object.defineProperty(a,u,Object.getOwnPropertyDescriptor(i,u))})}return a}function jo(a){"@babel/helpers - typeof";return jo=typeof Symbol=="function"&&typeof Symbol.iterator=="symbol"?function(r){return typeof r}:function(r){return r&&typeof Symbol=="function"&&r.constructor===Symbol&&r!==Symbol.prototype?"symbol":typeof r},jo(a)}function Yl(a,r,i){return r in a?Object.defineProperty(a,r,{value:i,enumerable:!0,configurable:!0,writable:!0}):a[r]=i,a}function N2(a,r){if(a==null)return{};var i={},u=Object.keys(a),s,f;for(f=0;f<u.length;f++)s=u[f],!(r.indexOf(s)>=0)&&(i[s]=a[s]);return i}function U2(a,r){if(a==null)return{};var i=N2(a,r),u,s;if(Object.getOwnPropertySymbols){var f=Object.getOwnPropertySymbols(a);for(s=0;s<f.length;s++)u=f[s],!(r.indexOf(u)>=0)&&Object.prototype.propertyIsEnumerable.call(a,u)&&(i[u]=a[u])}return i}function Hs(a){return q2(a)||H2(a)||L2(a)||B2()}function q2(a){if(Array.isArray(a))return Ls(a)}function H2(a){if(typeof Symbol<"u"&&a[Symbol.iterator]!=null||a["@@iterator"]!=null)return Array.from(a)}function L2(a,r){if(a){if(typeof a=="string")return Ls(a,r);var i=Object.prototype.toString.call(a).slice(8,-1);if(i==="Object"&&a.constructor&&(i=a.constructor.name),i==="Map"||i==="Set")return Array.from(a);if(i==="Arguments"||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(i))return Ls(a,r)}}function Ls(a,r){(r==null||r>a.length)&&(r=a.length);for(var i=0,u=new Array(r);i<r;i++)u[i]=a[i];return u}function B2(){throw new TypeError(`Invalid attempt to spread non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`)}function V2(a){var r,i=a.beat,u=a.fade,s=a.beatFade,f=a.bounce,m=a.shake,v=a.flash,h=a.spin,p=a.spinPulse,g=a.spinReverse,z=a.pulse,M=a.fixedWidth,w=a.inverse,A=a.border,E=a.listItem,R=a.flip,q=a.size,N=a.rotation,V=a.pull,F=(r={"fa-beat":i,"fa-fade":u,"fa-beat-fade":s,"fa-bounce":f,"fa-shake":m,"fa-flash":v,"fa-spin":h,"fa-spin-reverse":g,"fa-spin-pulse":p,"fa-pulse":z,"fa-fw":M,"fa-inverse":w,"fa-border":A,"fa-li":E,"fa-flip":R===!0,"fa-flip-horizontal":R==="horizontal"||R==="both","fa-flip-vertical":R==="vertical"||R==="both"},Yl(r,"fa-".concat(q),typeof q<"u"&&q!==null),Yl(r,"fa-rotate-".concat(N),typeof N<"u"&&N!==null&&N!==0),Yl(r,"fa-pull-".concat(V),typeof V<"u"&&V!==null),Yl(r,"fa-swap-opacity",a.swapOpacity),r);return Object.keys(F).map(function($){return F[$]?$:null}).filter(function($){return $})}function j2(a){return a=a-0,a===a}function _p(a){return j2(a)?a:(a=a.replace(/[\-_\s]+(.)?/g,function(r,i){return i?i.toUpperCase():""}),a.substr(0,1).toLowerCase()+a.substr(1))}var Y2=["style"];function X2(a){return a.charAt(0).toUpperCase()+a.slice(1)}function G2(a){return a.split(";").map(function(r){return r.trim()}).filter(function(r){return r}).reduce(function(r,i){var u=i.indexOf(":"),s=_p(i.slice(0,u)),f=i.slice(u+1).trim();return s.startsWith("webkit")?r[X2(s)]=f:r[s]=f,r},{})}function zp(a,r){var i=arguments.length>2&&arguments[2]!==void 0?arguments[2]:{};if(typeof r=="string")return r;var u=(r.children||[]).map(function(h){return zp(a,h)}),s=Object.keys(r.attributes||{}).reduce(function(h,p){var g=r.attributes[p];switch(p){case"class":h.attrs.className=g,delete r.attributes.class;break;case"style":h.attrs.style=G2(g);break;default:p.indexOf("aria-")===0||p.indexOf("data-")===0?h.attrs[p.toLowerCase()]=g:h.attrs[_p(p)]=g}return h},{attrs:{}}),f=i.style,m=f===void 0?{}:f,v=U2(i,Y2);return s.attrs.style=yn(yn({},s.attrs.style),m),a.apply(void 0,[r.tag,yn(yn({},s.attrs),v)].concat(Hs(u)))}var Dp=!1;try{Dp=!0}catch{}function Q2(){if(!Dp&&console&&typeof console.error=="function"){var a;(a=console).error.apply(a,arguments)}}function mm(a){if(a&&jo(a)==="object"&&a.prefix&&a.iconName&&a.icon)return a;if(zs.icon)return zs.icon(a);if(a===null)return null;if(a&&jo(a)==="object"&&a.prefix&&a.iconName)return a;if(Array.isArray(a)&&a.length===2)return{prefix:a[0],iconName:a[1]};if(typeof a=="string")return{prefix:"fas",iconName:a}}function Es(a,r){return Array.isArray(r)&&r.length>0||!Array.isArray(r)&&r?Yl({},a,r):{}}var pm={border:!1,className:"",mask:null,maskId:null,fixedWidth:!1,inverse:!1,flip:!1,icon:null,listItem:!1,pull:null,pulse:!1,rotation:null,size:null,spin:!1,spinPulse:!1,spinReverse:!1,beat:!1,fade:!1,beatFade:!1,bounce:!1,shake:!1,symbol:!1,title:"",titleId:null,transform:null,swapOpacity:!1},wp=X.forwardRef(function(a,r){var i=yn(yn({},pm),a),u=i.icon,s=i.mask,f=i.symbol,m=i.className,v=i.title,h=i.titleId,p=i.maskId,g=mm(u),z=Es("classes",[].concat(Hs(V2(i)),Hs((m||"").split(" ")))),M=Es("transform",typeof i.transform=="string"?zs.transform(i.transform):i.transform),w=Es("mask",mm(s)),A=dg(g,yn(yn(yn(yn({},z),M),w),{},{symbol:f,title:v,titleId:h,maskId:p}));if(!A)return Q2("Could not find icon",g),null;var E=A.abstract,R={ref:r};return Object.keys(i).forEach(function(q){pm.hasOwnProperty(q)||(R[q]=i[q])}),Z2(E[0],R)});wp.displayName="FontAwesomeIcon";wp.propTypes={beat:Oe.bool,border:Oe.bool,beatFade:Oe.bool,bounce:Oe.bool,className:Oe.string,fade:Oe.bool,flash:Oe.bool,mask:Oe.oneOfType([Oe.object,Oe.array,Oe.string]),maskId:Oe.string,fixedWidth:Oe.bool,inverse:Oe.bool,flip:Oe.oneOf([!0,!1,"horizontal","vertical","both"]),icon:Oe.oneOfType([Oe.object,Oe.array,Oe.string]),listItem:Oe.bool,pull:Oe.oneOf(["right","left"]),pulse:Oe.bool,rotation:Oe.oneOf([0,90,180,270]),shake:Oe.bool,size:Oe.oneOf(["2xs","xs","sm","lg","xl","2xl","1x","2x","3x","4x","5x","6x","7x","8x","9x","10x"]),spin:Oe.bool,spinPulse:Oe.bool,spinReverse:Oe.bool,symbol:Oe.oneOfType([Oe.bool,Oe.string]),title:Oe.string,titleId:Oe.string,transform:Oe.oneOfType([Oe.string,Oe.object]),swapOpacity:Oe.bool};var Z2=zp.bind(null,X.createElement);function ut(a,r){r===void 0&&(r={});var i=r.insertAt;if(a&&typeof document<"u"){var u=document.head||document.getElementsByTagName("head")[0],s=document.createElement("style");s.type="text/css",i==="top"&&u.firstChild?u.insertBefore(s,u.firstChild):u.appendChild(s),s.styleSheet?s.styleSheet.cssText=a:s.appendChild(document.createTextNode(a))}}ut(`.react-loading-indicator-normalize,
[class$=rli-bounding-box] {
  font-size: 1rem;
  display: inline-block;
  box-sizing: border-box;
  text-align: unset;
  isolation: isolate;
}

.rli-d-i-b {
  display: inline-block;
}

.rli-text-format {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  font-weight: 600;
  width: 90%;
  text-transform: uppercase;
  text-align: center;
  font-size: 0.7em;
  letter-spacing: 0.5px;
  font-family: system-ui, -apple-system, BlinkMacSystemFont, "Avenir Next", "Avenir", "Segoe UI", "Lucida Grande", "Helvetica Neue", "Helvetica", "Fira Sans", "Roboto", "Noto", "Droid Sans", "Cantarell", "Oxygen", "Ubuntu", "Franklin Gothic Medium", "Century Gothic", "Liberation Sans", sans-serif;
}`);var Ae=function(){return Ae=Object.assign||function(a){for(var r,i=1,u=arguments.length;i<u;i++)for(var s in r=arguments[i])Object.prototype.hasOwnProperty.call(r,s)&&(a[s]=r[s]);return a},Ae.apply(this,arguments)};function Yo(a){return Yo=typeof Symbol=="function"&&typeof Symbol.iterator=="symbol"?function(r){return typeof r}:function(r){return r&&typeof Symbol=="function"&&r.constructor===Symbol&&r!==Symbol.prototype?"symbol":typeof r},Yo(a)}var $2=/^\s+/,P2=/\s+$/;function ae(a,r){if(r=r||{},(a=a||"")instanceof ae)return a;if(!(this instanceof ae))return new ae(a,r);var i=function(u){var s={r:0,g:0,b:0},f=1,m=null,v=null,h=null,p=!1,g=!1;typeof u=="string"&&(u=function(A){A=A.replace($2,"").replace(P2,"").toLowerCase();var E,R=!1;if(Bs[A])A=Bs[A],R=!0;else if(A=="transparent")return{r:0,g:0,b:0,a:0,format:"name"};return(E=on.rgb.exec(A))?{r:E[1],g:E[2],b:E[3]}:(E=on.rgba.exec(A))?{r:E[1],g:E[2],b:E[3],a:E[4]}:(E=on.hsl.exec(A))?{h:E[1],s:E[2],l:E[3]}:(E=on.hsla.exec(A))?{h:E[1],s:E[2],l:E[3],a:E[4]}:(E=on.hsv.exec(A))?{h:E[1],s:E[2],v:E[3]}:(E=on.hsva.exec(A))?{h:E[1],s:E[2],v:E[3],a:E[4]}:(E=on.hex8.exec(A))?{r:Lt(E[1]),g:Lt(E[2]),b:Lt(E[3]),a:Sm(E[4]),format:R?"name":"hex8"}:(E=on.hex6.exec(A))?{r:Lt(E[1]),g:Lt(E[2]),b:Lt(E[3]),format:R?"name":"hex"}:(E=on.hex4.exec(A))?{r:Lt(E[1]+""+E[1]),g:Lt(E[2]+""+E[2]),b:Lt(E[3]+""+E[3]),a:Sm(E[4]+""+E[4]),format:R?"name":"hex8"}:(E=on.hex3.exec(A))?{r:Lt(E[1]+""+E[1]),g:Lt(E[2]+""+E[2]),b:Lt(E[3]+""+E[3]),format:R?"name":"hex"}:!1}(u)),Yo(u)=="object"&&(jn(u.r)&&jn(u.g)&&jn(u.b)?(z=u.r,M=u.g,w=u.b,s={r:255*je(z,255),g:255*je(M,255),b:255*je(w,255)},p=!0,g=String(u.r).substr(-1)==="%"?"prgb":"rgb"):jn(u.h)&&jn(u.s)&&jn(u.v)?(m=Jr(u.s),v=Jr(u.v),s=function(A,E,R){A=6*je(A,360),E=je(E,100),R=je(R,100);var q=Math.floor(A),N=A-q,V=R*(1-E),F=R*(1-N*E),$=R*(1-(1-N)*E),me=q%6,pe=[R,F,V,V,$,R][me],ve=[$,R,R,F,V,V][me],G=[V,V,$,R,R,F][me];return{r:255*pe,g:255*ve,b:255*G}}(u.h,m,v),p=!0,g="hsv"):jn(u.h)&&jn(u.s)&&jn(u.l)&&(m=Jr(u.s),h=Jr(u.l),s=function(A,E,R){var q,N,V;function F(pe,ve,G){return G<0&&(G+=1),G>1&&(G-=1),G<1/6?pe+6*(ve-pe)*G:G<.5?ve:G<2/3?pe+(ve-pe)*(2/3-G)*6:pe}if(A=je(A,360),E=je(E,100),R=je(R,100),E===0)q=N=V=R;else{var $=R<.5?R*(1+E):R+E-R*E,me=2*R-$;q=F(me,$,A+1/3),N=F(me,$,A),V=F(me,$,A-1/3)}return{r:255*q,g:255*N,b:255*V}}(u.h,m,h),p=!0,g="hsl"),u.hasOwnProperty("a")&&(f=u.a));var z,M,w;return f=Rp(f),{ok:p,format:u.format||g,r:Math.min(255,Math.max(s.r,0)),g:Math.min(255,Math.max(s.g,0)),b:Math.min(255,Math.max(s.b,0)),a:f}}(a);this._originalInput=a,this._r=i.r,this._g=i.g,this._b=i.b,this._a=i.a,this._roundA=Math.round(100*this._a)/100,this._format=r.format||i.format,this._gradientType=r.gradientType,this._r<1&&(this._r=Math.round(this._r)),this._g<1&&(this._g=Math.round(this._g)),this._b<1&&(this._b=Math.round(this._b)),this._ok=i.ok}function vm(a,r,i){a=je(a,255),r=je(r,255),i=je(i,255);var u,s,f=Math.max(a,r,i),m=Math.min(a,r,i),v=(f+m)/2;if(f==m)u=s=0;else{var h=f-m;switch(s=v>.5?h/(2-f-m):h/(f+m),f){case a:u=(r-i)/h+(r<i?6:0);break;case r:u=(i-a)/h+2;break;case i:u=(a-r)/h+4}u/=6}return{h:u,s,l:v}}function bm(a,r,i){a=je(a,255),r=je(r,255),i=je(i,255);var u,s,f=Math.max(a,r,i),m=Math.min(a,r,i),v=f,h=f-m;if(s=f===0?0:h/f,f==m)u=0;else{switch(f){case a:u=(r-i)/h+(r<i?6:0);break;case r:u=(i-a)/h+2;break;case i:u=(a-r)/h+4}u/=6}return{h:u,s,v}}function gm(a,r,i,u){var s=[fn(Math.round(a).toString(16)),fn(Math.round(r).toString(16)),fn(Math.round(i).toString(16))];return u&&s[0].charAt(0)==s[0].charAt(1)&&s[1].charAt(0)==s[1].charAt(1)&&s[2].charAt(0)==s[2].charAt(1)?s[0].charAt(0)+s[1].charAt(0)+s[2].charAt(0):s.join("")}function ym(a,r,i,u){return[fn(Mp(u)),fn(Math.round(a).toString(16)),fn(Math.round(r).toString(16)),fn(Math.round(i).toString(16))].join("")}function F2(a,r){r=r===0?0:r||10;var i=ae(a).toHsl();return i.s-=r/100,i.s=ru(i.s),ae(i)}function K2(a,r){r=r===0?0:r||10;var i=ae(a).toHsl();return i.s+=r/100,i.s=ru(i.s),ae(i)}function J2(a){return ae(a).desaturate(100)}function W2(a,r){r=r===0?0:r||10;var i=ae(a).toHsl();return i.l+=r/100,i.l=ru(i.l),ae(i)}function I2(a,r){r=r===0?0:r||10;var i=ae(a).toRgb();return i.r=Math.max(0,Math.min(255,i.r-Math.round(-r/100*255))),i.g=Math.max(0,Math.min(255,i.g-Math.round(-r/100*255))),i.b=Math.max(0,Math.min(255,i.b-Math.round(-r/100*255))),ae(i)}function e5(a,r){r=r===0?0:r||10;var i=ae(a).toHsl();return i.l-=r/100,i.l=ru(i.l),ae(i)}function t5(a,r){var i=ae(a).toHsl(),u=(i.h+r)%360;return i.h=u<0?360+u:u,ae(i)}function n5(a){var r=ae(a).toHsl();return r.h=(r.h+180)%360,ae(r)}function xm(a,r){if(isNaN(r)||r<=0)throw new Error("Argument to polyad must be a positive number");for(var i=ae(a).toHsl(),u=[ae(a)],s=360/r,f=1;f<r;f++)u.push(ae({h:(i.h+f*s)%360,s:i.s,l:i.l}));return u}function a5(a){var r=ae(a).toHsl(),i=r.h;return[ae(a),ae({h:(i+72)%360,s:r.s,l:r.l}),ae({h:(i+216)%360,s:r.s,l:r.l})]}function l5(a,r,i){r=r||6,i=i||30;var u=ae(a).toHsl(),s=360/i,f=[ae(a)];for(u.h=(u.h-(s*r>>1)+720)%360;--r;)u.h=(u.h+s)%360,f.push(ae(u));return f}function r5(a,r){r=r||6;for(var i=ae(a).toHsv(),u=i.h,s=i.s,f=i.v,m=[],v=1/r;r--;)m.push(ae({h:u,s,v:f})),f=(f+v)%1;return m}ae.prototype={isDark:function(){return this.getBrightness()<128},isLight:function(){return!this.isDark()},isValid:function(){return this._ok},getOriginalInput:function(){return this._originalInput},getFormat:function(){return this._format},getAlpha:function(){return this._a},getBrightness:function(){var a=this.toRgb();return(299*a.r+587*a.g+114*a.b)/1e3},getLuminance:function(){var a,r,i,u=this.toRgb();return a=u.r/255,r=u.g/255,i=u.b/255,.2126*(a<=.03928?a/12.92:Math.pow((a+.055)/1.055,2.4))+.7152*(r<=.03928?r/12.92:Math.pow((r+.055)/1.055,2.4))+.0722*(i<=.03928?i/12.92:Math.pow((i+.055)/1.055,2.4))},setAlpha:function(a){return this._a=Rp(a),this._roundA=Math.round(100*this._a)/100,this},toHsv:function(){var a=bm(this._r,this._g,this._b);return{h:360*a.h,s:a.s,v:a.v,a:this._a}},toHsvString:function(){var a=bm(this._r,this._g,this._b),r=Math.round(360*a.h),i=Math.round(100*a.s),u=Math.round(100*a.v);return this._a==1?"hsv("+r+", "+i+"%, "+u+"%)":"hsva("+r+", "+i+"%, "+u+"%, "+this._roundA+")"},toHsl:function(){var a=vm(this._r,this._g,this._b);return{h:360*a.h,s:a.s,l:a.l,a:this._a}},toHslString:function(){var a=vm(this._r,this._g,this._b),r=Math.round(360*a.h),i=Math.round(100*a.s),u=Math.round(100*a.l);return this._a==1?"hsl("+r+", "+i+"%, "+u+"%)":"hsla("+r+", "+i+"%, "+u+"%, "+this._roundA+")"},toHex:function(a){return gm(this._r,this._g,this._b,a)},toHexString:function(a){return"#"+this.toHex(a)},toHex8:function(a){return function(r,i,u,s,f){var m=[fn(Math.round(r).toString(16)),fn(Math.round(i).toString(16)),fn(Math.round(u).toString(16)),fn(Mp(s))];return f&&m[0].charAt(0)==m[0].charAt(1)&&m[1].charAt(0)==m[1].charAt(1)&&m[2].charAt(0)==m[2].charAt(1)&&m[3].charAt(0)==m[3].charAt(1)?m[0].charAt(0)+m[1].charAt(0)+m[2].charAt(0)+m[3].charAt(0):m.join("")}(this._r,this._g,this._b,this._a,a)},toHex8String:function(a){return"#"+this.toHex8(a)},toRgb:function(){return{r:Math.round(this._r),g:Math.round(this._g),b:Math.round(this._b),a:this._a}},toRgbString:function(){return this._a==1?"rgb("+Math.round(this._r)+", "+Math.round(this._g)+", "+Math.round(this._b)+")":"rgba("+Math.round(this._r)+", "+Math.round(this._g)+", "+Math.round(this._b)+", "+this._roundA+")"},toPercentageRgb:function(){return{r:Math.round(100*je(this._r,255))+"%",g:Math.round(100*je(this._g,255))+"%",b:Math.round(100*je(this._b,255))+"%",a:this._a}},toPercentageRgbString:function(){return this._a==1?"rgb("+Math.round(100*je(this._r,255))+"%, "+Math.round(100*je(this._g,255))+"%, "+Math.round(100*je(this._b,255))+"%)":"rgba("+Math.round(100*je(this._r,255))+"%, "+Math.round(100*je(this._g,255))+"%, "+Math.round(100*je(this._b,255))+"%, "+this._roundA+")"},toName:function(){return this._a===0?"transparent":!(this._a<1)&&(i5[gm(this._r,this._g,this._b,!0)]||!1)},toFilter:function(a){var r="#"+ym(this._r,this._g,this._b,this._a),i=r,u=this._gradientType?"GradientType = 1, ":"";if(a){var s=ae(a);i="#"+ym(s._r,s._g,s._b,s._a)}return"progid:DXImageTransform.Microsoft.gradient("+u+"startColorstr="+r+",endColorstr="+i+")"},toString:function(a){var r=!!a;a=a||this._format;var i=!1,u=this._a<1&&this._a>=0;return r||!u||a!=="hex"&&a!=="hex6"&&a!=="hex3"&&a!=="hex4"&&a!=="hex8"&&a!=="name"?(a==="rgb"&&(i=this.toRgbString()),a==="prgb"&&(i=this.toPercentageRgbString()),a!=="hex"&&a!=="hex6"||(i=this.toHexString()),a==="hex3"&&(i=this.toHexString(!0)),a==="hex4"&&(i=this.toHex8String(!0)),a==="hex8"&&(i=this.toHex8String()),a==="name"&&(i=this.toName()),a==="hsl"&&(i=this.toHslString()),a==="hsv"&&(i=this.toHsvString()),i||this.toHexString()):a==="name"&&this._a===0?this.toName():this.toRgbString()},clone:function(){return ae(this.toString())},_applyModification:function(a,r){var i=a.apply(null,[this].concat([].slice.call(r)));return this._r=i._r,this._g=i._g,this._b=i._b,this.setAlpha(i._a),this},lighten:function(){return this._applyModification(W2,arguments)},brighten:function(){return this._applyModification(I2,arguments)},darken:function(){return this._applyModification(e5,arguments)},desaturate:function(){return this._applyModification(F2,arguments)},saturate:function(){return this._applyModification(K2,arguments)},greyscale:function(){return this._applyModification(J2,arguments)},spin:function(){return this._applyModification(t5,arguments)},_applyCombination:function(a,r){return a.apply(null,[this].concat([].slice.call(r)))},analogous:function(){return this._applyCombination(l5,arguments)},complement:function(){return this._applyCombination(n5,arguments)},monochromatic:function(){return this._applyCombination(r5,arguments)},splitcomplement:function(){return this._applyCombination(a5,arguments)},triad:function(){return this._applyCombination(xm,[3])},tetrad:function(){return this._applyCombination(xm,[4])}},ae.fromRatio=function(a,r){if(Yo(a)=="object"){var i={};for(var u in a)a.hasOwnProperty(u)&&(i[u]=u==="a"?a[u]:Jr(a[u]));a=i}return ae(a,r)},ae.equals=function(a,r){return!(!a||!r)&&ae(a).toRgbString()==ae(r).toRgbString()},ae.random=function(){return ae.fromRatio({r:Math.random(),g:Math.random(),b:Math.random()})},ae.mix=function(a,r,i){i=i===0?0:i||50;var u=ae(a).toRgb(),s=ae(r).toRgb(),f=i/100;return ae({r:(s.r-u.r)*f+u.r,g:(s.g-u.g)*f+u.g,b:(s.b-u.b)*f+u.b,a:(s.a-u.a)*f+u.a})},ae.readability=function(a,r){var i=ae(a),u=ae(r);return(Math.max(i.getLuminance(),u.getLuminance())+.05)/(Math.min(i.getLuminance(),u.getLuminance())+.05)},ae.isReadable=function(a,r,i){var u,s,f=ae.readability(a,r);switch(s=!1,(u=function(m){var v,h;return v=((m=m||{level:"AA",size:"small"}).level||"AA").toUpperCase(),h=(m.size||"small").toLowerCase(),v!=="AA"&&v!=="AAA"&&(v="AA"),h!=="small"&&h!=="large"&&(h="small"),{level:v,size:h}}(i)).level+u.size){case"AAsmall":case"AAAlarge":s=f>=4.5;break;case"AAlarge":s=f>=3;break;case"AAAsmall":s=f>=7}return s},ae.mostReadable=function(a,r,i){var u,s,f,m,v=null,h=0;s=(i=i||{}).includeFallbackColors,f=i.level,m=i.size;for(var p=0;p<r.length;p++)(u=ae.readability(a,r[p]))>h&&(h=u,v=ae(r[p]));return ae.isReadable(a,v,{level:f,size:m})||!s?v:(i.includeFallbackColors=!1,ae.mostReadable(a,["#fff","#000"],i))};var Bs=ae.names={aliceblue:"f0f8ff",antiquewhite:"faebd7",aqua:"0ff",aquamarine:"7fffd4",azure:"f0ffff",beige:"f5f5dc",bisque:"ffe4c4",black:"000",blanchedalmond:"ffebcd",blue:"00f",blueviolet:"8a2be2",brown:"a52a2a",burlywood:"deb887",burntsienna:"ea7e5d",cadetblue:"5f9ea0",chartreuse:"7fff00",chocolate:"d2691e",coral:"ff7f50",cornflowerblue:"6495ed",cornsilk:"fff8dc",crimson:"dc143c",cyan:"0ff",darkblue:"00008b",darkcyan:"008b8b",darkgoldenrod:"b8860b",darkgray:"a9a9a9",darkgreen:"006400",darkgrey:"a9a9a9",darkkhaki:"bdb76b",darkmagenta:"8b008b",darkolivegreen:"556b2f",darkorange:"ff8c00",darkorchid:"9932cc",darkred:"8b0000",darksalmon:"e9967a",darkseagreen:"8fbc8f",darkslateblue:"483d8b",darkslategray:"2f4f4f",darkslategrey:"2f4f4f",darkturquoise:"00ced1",darkviolet:"9400d3",deeppink:"ff1493",deepskyblue:"00bfff",dimgray:"696969",dimgrey:"696969",dodgerblue:"1e90ff",firebrick:"b22222",floralwhite:"fffaf0",forestgreen:"228b22",fuchsia:"f0f",gainsboro:"dcdcdc",ghostwhite:"f8f8ff",gold:"ffd700",goldenrod:"daa520",gray:"808080",green:"008000",greenyellow:"adff2f",grey:"808080",honeydew:"f0fff0",hotpink:"ff69b4",indianred:"cd5c5c",indigo:"4b0082",ivory:"fffff0",khaki:"f0e68c",lavender:"e6e6fa",lavenderblush:"fff0f5",lawngreen:"7cfc00",lemonchiffon:"fffacd",lightblue:"add8e6",lightcoral:"f08080",lightcyan:"e0ffff",lightgoldenrodyellow:"fafad2",lightgray:"d3d3d3",lightgreen:"90ee90",lightgrey:"d3d3d3",lightpink:"ffb6c1",lightsalmon:"ffa07a",lightseagreen:"20b2aa",lightskyblue:"87cefa",lightslategray:"789",lightslategrey:"789",lightsteelblue:"b0c4de",lightyellow:"ffffe0",lime:"0f0",limegreen:"32cd32",linen:"faf0e6",magenta:"f0f",maroon:"800000",mediumaquamarine:"66cdaa",mediumblue:"0000cd",mediumorchid:"ba55d3",mediumpurple:"9370db",mediumseagreen:"3cb371",mediumslateblue:"7b68ee",mediumspringgreen:"00fa9a",mediumturquoise:"48d1cc",mediumvioletred:"c71585",midnightblue:"191970",mintcream:"f5fffa",mistyrose:"ffe4e1",moccasin:"ffe4b5",navajowhite:"ffdead",navy:"000080",oldlace:"fdf5e6",olive:"808000",olivedrab:"6b8e23",orange:"ffa500",orangered:"ff4500",orchid:"da70d6",palegoldenrod:"eee8aa",palegreen:"98fb98",paleturquoise:"afeeee",palevioletred:"db7093",papayawhip:"ffefd5",peachpuff:"ffdab9",peru:"cd853f",pink:"ffc0cb",plum:"dda0dd",powderblue:"b0e0e6",purple:"800080",rebeccapurple:"663399",red:"f00",rosybrown:"bc8f8f",royalblue:"4169e1",saddlebrown:"8b4513",salmon:"fa8072",sandybrown:"f4a460",seagreen:"2e8b57",seashell:"fff5ee",sienna:"a0522d",silver:"c0c0c0",skyblue:"87ceeb",slateblue:"6a5acd",slategray:"708090",slategrey:"708090",snow:"fffafa",springgreen:"00ff7f",steelblue:"4682b4",tan:"d2b48c",teal:"008080",thistle:"d8bfd8",tomato:"ff6347",turquoise:"40e0d0",violet:"ee82ee",wheat:"f5deb3",white:"fff",whitesmoke:"f5f5f5",yellow:"ff0",yellowgreen:"9acd32"},i5=ae.hexNames=function(a){var r={};for(var i in a)a.hasOwnProperty(i)&&(r[a[i]]=i);return r}(Bs);function Rp(a){return a=parseFloat(a),(isNaN(a)||a<0||a>1)&&(a=1),a}function je(a,r){(function(u){return typeof u=="string"&&u.indexOf(".")!=-1&&parseFloat(u)===1})(a)&&(a="100%");var i=function(u){return typeof u=="string"&&u.indexOf("%")!=-1}(a);return a=Math.min(r,Math.max(0,parseFloat(a))),i&&(a=parseInt(a*r,10)/100),Math.abs(a-r)<1e-6?1:a%r/parseFloat(r)}function ru(a){return Math.min(1,Math.max(0,a))}function Lt(a){return parseInt(a,16)}function fn(a){return a.length==1?"0"+a:""+a}function Jr(a){return a<=1&&(a=100*a+"%"),a}function Mp(a){return Math.round(255*parseFloat(a)).toString(16)}function Sm(a){return Lt(a)/255}var ga,zo,Do,on=(zo="[\\s|\\(]+("+(ga="(?:[-\\+]?\\d*\\.\\d+%?)|(?:[-\\+]?\\d+%?)")+")[,|\\s]+("+ga+")[,|\\s]+("+ga+")\\s*\\)?",Do="[\\s|\\(]+("+ga+")[,|\\s]+("+ga+")[,|\\s]+("+ga+")[,|\\s]+("+ga+")\\s*\\)?",{CSS_UNIT:new RegExp(ga),rgb:new RegExp("rgb"+zo),rgba:new RegExp("rgba"+Do),hsl:new RegExp("hsl"+zo),hsla:new RegExp("hsla"+Do),hsv:new RegExp("hsv"+zo),hsva:new RegExp("hsva"+Do),hex3:/^#?([0-9a-fA-F]{1})([0-9a-fA-F]{1})([0-9a-fA-F]{1})$/,hex6:/^#?([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})$/,hex4:/^#?([0-9a-fA-F]{1})([0-9a-fA-F]{1})([0-9a-fA-F]{1})([0-9a-fA-F]{1})$/,hex8:/^#?([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})$/});function jn(a){return!!on.CSS_UNIT.exec(a)}var oi=function(a,r){var i=(typeof a=="string"?parseInt(a):a)||0;if(i>=-5&&i<=5){var u=i,s=parseFloat(r),f=s+u*(s/5)*-1;return(f==0||f<=Number.EPSILON)&&(f=.1),{animationPeriod:f+"s"}}return{animationPeriod:r}},ui=function(a,r){var i=a||{},u="";switch(r){case"small":u="12px";break;case"medium":u="16px";break;case"large":u="20px";break;default:u=void 0}var s={};if(i.fontSize){var f=i.fontSize;s=function(m,v){var h={};for(var p in m)Object.prototype.hasOwnProperty.call(m,p)&&v.indexOf(p)<0&&(h[p]=m[p]);if(m!=null&&typeof Object.getOwnPropertySymbols=="function"){var g=0;for(p=Object.getOwnPropertySymbols(m);g<p.length;g++)v.indexOf(p[g])<0&&Object.prototype.propertyIsEnumerable.call(m,p[g])&&(h[p[g]]=m[p[g]])}return h}(i,["fontSize"]),u=f}return{fontSize:u,styles:s}},o5={color:"currentColor",mixBlendMode:"difference",width:"unset",display:"block",paddingTop:"2px"},ci=function(a){var r=a.className,i=a.text,u=a.textColor,s=a.staticText,f=a.style;return i?X.createElement("span",{className:"rli-d-i-b rli-text-format ".concat(r||"").trim(),style:Ae(Ae(Ae({},s&&o5),u&&{color:u,mixBlendMode:"unset"}),f&&f)},typeof i=="string"&&i.length?i:"loading"):null},Gn="rgb(50, 205, 50)";function si(a,r){r===void 0&&(r=0);var i=[];return function u(s,f){return f===void 0&&(f=0),i.push.apply(i,s),i.length<f&&u(i,f),i.slice(0,f)}(a,r)}ut(`.atom-rli-bounding-box {
  --atom-phase1-rgb: 50, 205, 50;
  color: rgba(var(--atom-phase1-rgb), 1);
  font-size: 16px;
  position: relative;
  text-align: unset;
  isolation: isolate;
}
.atom-rli-bounding-box .atom-indicator {
  width: 6em;
  height: 6em;
  position: relative;
  perspective: 6em;
  overflow: hidden;
  color: rgba(var(--atom-phase1-rgb), 1);
  animation: calc(var(--rli-animation-duration, 1s) * 4) var(--rli-animation-function, linear) infinite u1qz6fj;
}
.atom-rli-bounding-box .atom-indicator::after, .atom-rli-bounding-box .atom-indicator::before {
  content: "";
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  width: 0.48em;
  height: 0.48em;
  margin: auto;
  border-radius: 50%;
  background-image: radial-gradient(circle at 35% 15%, rgba(var(--atom-phase1-rgb), 0.1), rgba(var(--atom-phase1-rgb), 0.3) 37%, rgba(var(--atom-phase1-rgb), 1) 100%);
  animation: calc(var(--rli-animation-duration, 1s) * 4) var(--rli-animation-function, linear) infinite u1qz6de;
}
.atom-rli-bounding-box .atom-indicator::before {
  filter: drop-shadow(0px 0px 0.0625em currentColor);
}
.atom-rli-bounding-box .atom-indicator .electron-orbit {
  color: rgba(var(--atom-phase1-rgb), 0.85);
  border: 0;
  border-left: 0.4em solid currentColor;
  box-sizing: border-box;
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  margin: auto;
  width: 4.8em;
  height: 4.8em;
  background-color: transparent;
  border-radius: 50%;
  transform-style: preserve-3d;
  animation: var(--rli-animation-duration, 1s) var(--rli-animation-function, linear) infinite u1qz6ex, calc(var(--rli-animation-duration, 1s) * 4) var(--rli-animation-function, linear) infinite u1qz6g6;
}
.atom-rli-bounding-box .atom-indicator .electron-orbit::after {
  content: "";
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  border-radius: 50%;
  color: rgba(var(--atom-phase1-rgb), 0.18);
  animation: calc(var(--rli-animation-duration, 1s) * 4) var(--rli-animation-function, linear) infinite u1qz6h4;
  border: 0.125em solid currentColor;
}
.atom-rli-bounding-box .atom-indicator .electron-orbit::before {
  content: "";
  width: 0.192em;
  height: 0.192em;
  position: absolute;
  border-radius: 50%;
  top: -0.096em;
  right: 0;
  bottom: 0;
  left: 0;
  margin: 0 auto;
  color: rgba(var(--atom-phase1-rgb), 1);
  box-shadow: 0px 0px 0.0625em 0.0625em currentColor, 0px 0px 0.0625em 0.125em currentColor;
  background-color: currentColor;
  transform: rotateY(-70deg);
  animation: var(--rli-animation-duration, 1s) var(--rli-animation-function, linear) infinite u1qz6e7, calc(var(--rli-animation-duration, 1s) * 4) var(--rli-animation-function, linear) infinite u1qz6fj;
}
.atom-rli-bounding-box .atom-indicator .electron-orbit:nth-of-type(1) {
  --orbit-vector-factor: -1;
  transform: rotateY(65deg) rotateX(calc(54deg * var(--orbit-vector-factor)));
}
.atom-rli-bounding-box .atom-indicator .electron-orbit:nth-of-type(2) {
  --orbit-vector-factor: 1;
  transform: rotateY(65deg) rotateX(calc(54deg * var(--orbit-vector-factor)));
}
.atom-rli-bounding-box .atom-indicator .electron-orbit:nth-of-type(3) {
  --orbit-vector-factor: 0;
  transform: rotateY(65deg) rotateX(calc(54deg * var(--orbit-vector-factor)));
  animation-delay: calc(var(--rli-animation-duration, 1s) * 0.5 * -1), calc(var(--rli-animation-duration, 1s) * 4 * -1);
}
.atom-rli-bounding-box .atom-indicator .electron-orbit:nth-of-type(3)::before {
  animation-delay: calc(var(--rli-animation-duration, 1s) * 0.5 * -1), calc(var(--rli-animation-duration, 1s) * 4 * -1);
}
.atom-rli-bounding-box .atom-text {
  color: currentColor;
  mix-blend-mode: difference;
  width: unset;
  display: block;
}

@property --atom-phase1-rgb {
  syntax: "<number>#";
  inherits: true;
  initial-value: 50, 205, 50;
}
@property --atom-phase2-rgb {
  syntax: "<number>#";
  inherits: true;
  initial-value: 50, 205, 50;
}
@property --atom-phase3-rgb {
  syntax: "<number>#";
  inherits: true;
  initial-value: 50, 205, 50;
}
@property --atom-phase4-rgb {
  syntax: "<number>#";
  inherits: true;
  initial-value: 50, 205, 50;
}
@property --rli-animation-duration {
  syntax: "<time>";
  inherits: true;
  initial-value: 1s;
}
@keyframes u1qz6ex {
  from {
    transform: rotateY(70deg) rotateX(calc(54deg * var(--orbit-vector-factor))) rotateZ(0deg);
  }
  to {
    transform: rotateY(70deg) rotateX(calc(54deg * var(--orbit-vector-factor))) rotateZ(360deg);
  }
}
@keyframes u1qz6e7 {
  from {
    transform: rotateY(-70deg) rotateX(0deg);
  }
  to {
    transform: rotateY(-70deg) rotateX(-360deg);
  }
}
@keyframes u1qz6de {
  100%, 0% {
    background-image: radial-gradient(circle at 35% 15%, rgba(var(--atom-phase1-rgb), 0.1), rgba(var(--atom-phase1-rgb), 0.3) 37%, rgba(var(--atom-phase1-rgb), 1) 100%);
  }
  20% {
    background-image: radial-gradient(circle at 35% 15%, rgba(var(--atom-phase1-rgb), 0.1), rgba(var(--atom-phase1-rgb), 0.3) 37%, rgba(var(--atom-phase1-rgb), 1) 100%);
  }
  25% {
    background-image: radial-gradient(circle at 35% 15%, rgba(var(--atom-phase2-rgb, var(--atom-phase1-rgb)), 0.1), rgba(var(--atom-phase2-rgb, var(--atom-phase1-rgb)), 0.3) 37%, rgba(var(--atom-phase2-rgb, var(--atom-phase1-rgb)), 1) 100%);
  }
  45% {
    background-image: radial-gradient(circle at 35% 15%, rgba(var(--atom-phase2-rgb, var(--atom-phase1-rgb)), 0.1), rgba(var(--atom-phase2-rgb, var(--atom-phase1-rgb)), 0.3) 37%, rgba(var(--atom-phase2-rgb, var(--atom-phase1-rgb)), 1) 100%);
  }
  50% {
    background-image: radial-gradient(circle at 35% 15%, rgba(var(--atom-phase3-rgb, var(--atom-phase1-rgb)), 0.1), rgba(var(--atom-phase3-rgb, var(--atom-phase1-rgb)), 0.3) 37%, rgba(var(--atom-phase3-rgb, var(--atom-phase1-rgb)), 1) 100%);
  }
  70% {
    background-image: radial-gradient(circle at 35% 15%, rgba(var(--atom-phase3-rgb, var(--atom-phase1-rgb)), 0.1), rgba(var(--atom-phase3-rgb, var(--atom-phase1-rgb)), 0.3) 37%, rgba(var(--atom-phase3-rgb, var(--atom-phase1-rgb)), 1) 100%);
  }
  75% {
    background-image: radial-gradient(circle at 35% 15%, rgba(var(--atom-phase4-rgb, var(--atom-phase1-rgb)), 0.1), rgba(var(--atom-phase4-rgb, var(--atom-phase1-rgb)), 0.3) 37%, rgba(var(--atom-phase4-rgb, var(--atom-phase1-rgb)), 1) 100%);
  }
  95% {
    background-image: radial-gradient(circle at 35% 15%, rgba(var(--atom-phase4-rgb, var(--atom-phase1-rgb)), 0.1), rgba(var(--atom-phase4-rgb, var(--atom-phase1-rgb)), 0.3) 37%, rgba(var(--atom-phase4-rgb, var(--atom-phase1-rgb)), 1) 100%);
  }
}
@keyframes u1qz6fj {
  100%, 0% {
    color: rgba(var(--atom-phase1-rgb), 1);
  }
  20% {
    color: rgba(var(--atom-phase1-rgb), 1);
  }
  25% {
    color: rgba(var(--atom-phase2-rgb, var(--atom-phase1-rgb)), 1);
  }
  45% {
    color: rgba(var(--atom-phase2-rgb, var(--atom-phase1-rgb)), 1);
  }
  50% {
    color: rgba(var(--atom-phase3-rgb, var(--atom-phase1-rgb)), 1);
  }
  70% {
    color: rgba(var(--atom-phase3-rgb, var(--atom-phase1-rgb)), 1);
  }
  75% {
    color: rgba(var(--atom-phase4-rgb, var(--atom-phase1-rgb)), 1);
  }
  95% {
    color: rgba(var(--atom-phase4-rgb, var(--atom-phase1-rgb)), 1);
  }
}
@keyframes u1qz6g6 {
  100%, 0% {
    color: rgba(var(--atom-phase1-rgb), 0.85);
  }
  20% {
    color: rgba(var(--atom-phase1-rgb), 0.85);
  }
  25% {
    color: rgba(var(--atom-phase2-rgb, var(--atom-phase1-rgb)), 0.85);
  }
  45% {
    color: rgba(var(--atom-phase2-rgb, var(--atom-phase1-rgb)), 0.85);
  }
  50% {
    color: rgba(var(--atom-phase3-rgb, var(--atom-phase1-rgb)), 0.85);
  }
  70% {
    color: rgba(var(--atom-phase3-rgb, var(--atom-phase1-rgb)), 0.85);
  }
  75% {
    color: rgba(var(--atom-phase4-rgb, var(--atom-phase1-rgb)), 0.85);
  }
  95% {
    color: rgba(var(--atom-phase4-rgb, var(--atom-phase1-rgb)), 0.85);
  }
}
@keyframes u1qz6h4 {
  100%, 0% {
    color: rgba(var(--atom-phase1-rgb), 0.18);
  }
  20% {
    color: rgba(var(--atom-phase1-rgb), 0.18);
  }
  25% {
    color: rgba(var(--atom-phase2-rgb, var(--atom-phase1-rgb)), 0.18);
  }
  45% {
    color: rgba(var(--atom-phase2-rgb, var(--atom-phase1-rgb)), 0.18);
  }
  50% {
    color: rgba(var(--atom-phase3-rgb, var(--atom-phase1-rgb)), 0.18);
  }
  70% {
    color: rgba(var(--atom-phase3-rgb, var(--atom-phase1-rgb)), 0.18);
  }
  75% {
    color: rgba(var(--atom-phase4-rgb, var(--atom-phase1-rgb)), 0.18);
  }
  95% {
    color: rgba(var(--atom-phase4-rgb, var(--atom-phase1-rgb)), 0.18);
  }
}`);ae(Gn).toRgb();Array.from({length:4},function(a,r){return"--atom-phase".concat(r+1,"-rgb")});ut(`.commet-rli-bounding-box {
  --commet-phase1-color: rgb(50, 205, 50);
  font-size: 16px;
  width: 6.85em;
  height: 6.85em;
  overflow: hidden;
  display: inline-block;
  box-sizing: border-box;
  position: relative;
  isolation: isolate;
}
.commet-rli-bounding-box .commet-indicator {
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  margin: auto;
  box-sizing: border-box;
  width: 6em;
  height: 6em;
  color: var(--commet-phase1-color);
  display: inline-block;
  isolation: isolate;
  position: absolute;
  z-index: 0;
  animation: calc(var(--rli-animation-duration, 1.2s) * 4) var(--rli-animation-function, cubic-bezier(0.08, 0.03, 0.91, 0.93)) infinite u1qz6k3;
}
.commet-rli-bounding-box .commet-indicator .commet-box {
  position: absolute;
  display: inline-block;
  top: 0;
  right: 0;
  bottom: 0;
  bottom: 0;
  left: 0;
  margin: auto;
  animation: u1qz6j2 var(--rli-animation-duration, 1.2s) var(--rli-animation-function, cubic-bezier(0.08, 0.03, 0.91, 0.93)) infinite;
}
.commet-rli-bounding-box .commet-indicator .commet-box:nth-of-type(1) {
  width: 100%;
  height: 100%;
  animation-direction: normal;
}
.commet-rli-bounding-box .commet-indicator .commet-box:nth-of-type(2) {
  width: 70%;
  height: 70%;
  animation-direction: reverse;
}
.commet-rli-bounding-box .commet-indicator .commet-box .commetball-box {
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  bottom: 0;
  left: 0;
  display: inline-block;
}
.commet-rli-bounding-box .commet-indicator .commet-box .commetball-box::before {
  content: "";
  width: 0.5em;
  height: 0.5em;
  border-radius: 50%;
  background-color: currentColor;
  position: absolute;
  top: -0.125em;
  left: 50%;
  transform: translateX(-50%);
  box-shadow: 0 0 0.2em 0em currentColor, 0 0 0.6em 0em currentColor;
}
.commet-rli-bounding-box .commet-indicator .commet-box .commet-trail {
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  bottom: 0;
  left: 0;
  border-radius: 50%;
  box-sizing: border-box;
  border-style: solid;
}
.commet-rli-bounding-box .commet-indicator .commet-box .commet-trail.trail1 {
  border-color: currentColor transparent transparent currentColor;
  border-width: 0.25em 0.25em 0 0;
  transform: rotateZ(-45deg);
}
.commet-rli-bounding-box .commet-indicator .commet-box .commet-trail.trail2 {
  border-color: currentColor currentColor transparent transparent;
  border-width: 0.25em 0 0 0.25em;
  transform: rotateZ(45deg);
}
.commet-rli-bounding-box .commet-text {
  mix-blend-mode: difference;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  color: var(--commet-phase1-color);
}

@property --commet-phase1-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --commet-phase2-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --commet-phase3-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --commet-phase4-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --rli-animation-duration {
  syntax: "<time>";
  inherits: true;
  initial-value: 1.2s;
}
@keyframes u1qz6j2 {
  to {
    transform: rotate(1turn);
  }
}
@keyframes u1qz6k3 {
  100%, 0% {
    color: var(--commet-phase1-color);
  }
  20% {
    color: var(--commet-phase1-color);
  }
  25% {
    color: var(--commet-phase2-color, var(--commet-phase1-color));
  }
  45% {
    color: var(--commet-phase2-color, var(--commet-phase1-color));
  }
  50% {
    color: var(--commet-phase3-color, var(--commet-phase1-color));
  }
  70% {
    color: var(--commet-phase3-color, var(--commet-phase1-color));
  }
  75% {
    color: var(--commet-phase4-color, var(--commet-phase1-color));
  }
  95% {
    color: var(--commet-phase4-color, var(--commet-phase1-color));
  }
}`);Array.from({length:4},function(a,r){return"--commet-phase".concat(r+1,"-color")});ut(`.OP-annulus-rli-bounding-box {
  --OP-annulus-phase1-color: rgb(50, 205, 50);
  font-size: 16px;
  display: inline-block;
}
.OP-annulus-rli-bounding-box .OP-annulus-indicator {
  width: 5em;
  height: 5em;
  color: var(--OP-annulus-phase1-color);
  display: inline-block;
  position: relative;
  z-index: 0;
}
.OP-annulus-rli-bounding-box .OP-annulus-indicator .whirl {
  animation: u1qz6pz calc(var(--rli-animation-duration, 1.5s) * 1.33) linear infinite;
  height: 100%;
  transform-origin: center center;
  width: 100%;
  position: absolute;
  top: 0;
  bottom: 0;
  left: 0;
  right: 0;
  margin: auto;
}
.OP-annulus-rli-bounding-box .OP-annulus-indicator .path {
  stroke-dasharray: 1, 125;
  stroke-dashoffset: 0;
  animation: var(--rli-animation-duration, 1.5s) var(--rli-animation-function, ease-in-out) infinite u1qz6r6, calc(var(--rli-animation-duration, 1.5s) * 4) var(--rli-animation-function, ease-in-out) infinite u1qz6sy;
  stroke-linecap: round;
}
.OP-annulus-rli-bounding-box .OP-annulus-text {
  mix-blend-mode: difference;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  z-index: -2;
}

@property --OP-annulus-phase1-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --OP-annulus-phase2-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --OP-annulus-phase3-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --OP-annulus-phase4-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --rli-animation-duration {
  syntax: "<time>";
  inherits: true;
  initial-value: 1.5s;
}
@keyframes u1qz6pz {
  100% {
    transform: rotate(360deg);
  }
}
@keyframes u1qz6r6 {
  0% {
    stroke-dasharray: 1, 125;
    stroke-dashoffset: 0;
  }
  50% {
    stroke-dasharray: 98, 125;
    stroke-dashoffset: -35px;
  }
  100% {
    stroke-dasharray: 98, 125;
    stroke-dashoffset: -124px;
  }
}
@keyframes u1qz6sy {
  100%, 0% {
    stroke: var(--OP-annulus-phase1-color);
  }
  22% {
    stroke: var(--OP-annulus-phase1-color);
  }
  25% {
    stroke: var(--OP-annulus-phase2-color, var(--OP-annulus-phase1-color));
  }
  42% {
    stroke: var(--OP-annulus-phase2-color, var(--OP-annulus-phase1-color));
  }
  50% {
    stroke: var(--OP-annulus-phase3-color, var(--OP-annulus-phase1-color));
  }
  72% {
    stroke: var(--OP-annulus-phase3-color, var(--OP-annulus-phase1-color));
  }
  75% {
    stroke: var(--OP-annulus-phase4-color, var(--OP-annulus-phase1-color));
  }
  97% {
    stroke: var(--OP-annulus-phase4-color, var(--OP-annulus-phase1-color));
  }
}`);var wo=Array.from({length:4},function(a,r){return"--OP-annulus-phase".concat(r+1,"-color")}),u5=function(a){var r,i=ui(a==null?void 0:a.style,a==null?void 0:a.size),u=i.styles,s=i.fontSize,f=a==null?void 0:a.easing,m=oi(a==null?void 0:a.speedPlus,"1.5s").animationPeriod,v=function(p){var g={},z=wo.length;if(p instanceof Array){for(var M=si(p,z),w=0;w<M.length&&!(w>=4);w++)g[wo[w]]=M[w];return g}try{if(typeof p!="string")throw new Error("Color String expected");for(var A=0;A<z;A++)g[wo[A]]=p}catch(E){for(E instanceof Error?console.warn("[".concat(E.message,']: Received "').concat(typeof p,'" instead with value, ').concat(JSON.stringify(p))):console.warn("".concat(JSON.stringify(p),' received in <OrbitProgress variant="disc" /> indicator cannot be processed. Using default instead!')),A=0;A<z;A++)g[wo[A]]=Gn}return g}((r=a==null?void 0:a.color)!==null&&r!==void 0?r:""),h=a!=null&&a.dense?4.3:2.9;return X.createElement("span",{className:"rli-d-i-b OP-annulus-rli-bounding-box",style:Ae(Ae(Ae(Ae(Ae({},s&&{fontSize:s}),m&&{"--rli-animation-duration":m}),f&&{"--rli-animation-function":f}),v),u),role:"status","aria-live":"polite","aria-label":"Loading"},X.createElement("span",{className:"rli-d-i-b OP-annulus-indicator"},X.createElement("svg",{className:"whirl",viewBox:"25 25 50 50"},X.createElement("circle",{className:"path",cx:"50",cy:"50",r:"20",fill:"none",strokeWidth:h,strokeMiterlimit:"10"})),X.createElement(ci,{className:"OP-annulus-text",text:a==null?void 0:a.text,textColor:a==null?void 0:a.textColor})))};function Os(a){return a&&a.Math===Math&&a}ut(`.OP-dotted-rli-bounding-box {
  --OP-dotted-phase1-color: rgb(50, 205, 50);
  font-size: 16px;
  box-sizing: border-box;
  display: inline-block;
}
.OP-dotted-rli-bounding-box .OP-dotted-indicator {
  width: 5em;
  height: 5em;
  color: var(--OP-dotted-phase1-color);
  display: inline-block;
  position: relative;
  z-index: 0;
}
.OP-dotted-rli-bounding-box .OP-dotted-indicator .OP-dotted-text {
  mix-blend-mode: difference;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  z-index: -2;
}
.OP-dotted-rli-bounding-box .OP-dotted-indicator .dot-shape-holder {
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  right: 0;
}
.OP-dotted-rli-bounding-box .OP-dotted-indicator .dot-shape-holder .dot {
  display: block;
  margin: 0 auto;
  width: 15%;
  height: 15%;
  background-color: currentColor;
  border-radius: 50%;
  animation: var(--rli-animation-duration, 1.2s) var(--rli-animation-function, ease-in-out) infinite u1qz6qy, calc(var(--rli-animation-duration, 1.2s) * 4) var(--rli-animation-function, ease-in-out) infinite u1qz6s0;
}
.OP-dotted-rli-bounding-box .OP-dotted-indicator .dot-shape-holder:nth-of-type(1) {
  transform: rotate(0deg);
}
.OP-dotted-rli-bounding-box .OP-dotted-indicator .dot-shape-holder:nth-of-type(1) .dot {
  animation-delay: calc(var(--rli-animation-duration, 1.2s) / 12 * 12 * -1);
}
.OP-dotted-rli-bounding-box .OP-dotted-indicator .dot-shape-holder:nth-of-type(2) {
  transform: rotate(30deg);
}
.OP-dotted-rli-bounding-box .OP-dotted-indicator .dot-shape-holder:nth-of-type(2) .dot {
  animation-delay: calc(var(--rli-animation-duration, 1.2s) / 12 * 11 * -1);
}
.OP-dotted-rli-bounding-box .OP-dotted-indicator .dot-shape-holder:nth-of-type(3) {
  transform: rotate(60deg);
}
.OP-dotted-rli-bounding-box .OP-dotted-indicator .dot-shape-holder:nth-of-type(3) .dot {
  animation-delay: calc(var(--rli-animation-duration, 1.2s) / 12 * 10 * -1);
}
.OP-dotted-rli-bounding-box .OP-dotted-indicator .dot-shape-holder:nth-of-type(4) {
  transform: rotate(90deg);
}
.OP-dotted-rli-bounding-box .OP-dotted-indicator .dot-shape-holder:nth-of-type(4) .dot {
  animation-delay: calc(var(--rli-animation-duration, 1.2s) / 12 * 9 * -1);
}
.OP-dotted-rli-bounding-box .OP-dotted-indicator .dot-shape-holder:nth-of-type(5) {
  transform: rotate(120deg);
}
.OP-dotted-rli-bounding-box .OP-dotted-indicator .dot-shape-holder:nth-of-type(5) .dot {
  animation-delay: calc(var(--rli-animation-duration, 1.2s) / 12 * 8 * -1);
}
.OP-dotted-rli-bounding-box .OP-dotted-indicator .dot-shape-holder:nth-of-type(6) {
  transform: rotate(150deg);
}
.OP-dotted-rli-bounding-box .OP-dotted-indicator .dot-shape-holder:nth-of-type(6) .dot {
  animation-delay: calc(var(--rli-animation-duration, 1.2s) / 12 * 7 * -1);
}
.OP-dotted-rli-bounding-box .OP-dotted-indicator .dot-shape-holder:nth-of-type(7) {
  transform: rotate(180deg);
}
.OP-dotted-rli-bounding-box .OP-dotted-indicator .dot-shape-holder:nth-of-type(7) .dot {
  animation-delay: calc(var(--rli-animation-duration, 1.2s) / 12 * 6 * -1);
}
.OP-dotted-rli-bounding-box .OP-dotted-indicator .dot-shape-holder:nth-of-type(8) {
  transform: rotate(210deg);
}
.OP-dotted-rli-bounding-box .OP-dotted-indicator .dot-shape-holder:nth-of-type(8) .dot {
  animation-delay: calc(var(--rli-animation-duration, 1.2s) / 12 * 5 * -1);
}
.OP-dotted-rli-bounding-box .OP-dotted-indicator .dot-shape-holder:nth-of-type(9) {
  transform: rotate(240deg);
}
.OP-dotted-rli-bounding-box .OP-dotted-indicator .dot-shape-holder:nth-of-type(9) .dot {
  animation-delay: calc(var(--rli-animation-duration, 1.2s) / 12 * 4 * -1);
}
.OP-dotted-rli-bounding-box .OP-dotted-indicator .dot-shape-holder:nth-of-type(10) {
  transform: rotate(270deg);
}
.OP-dotted-rli-bounding-box .OP-dotted-indicator .dot-shape-holder:nth-of-type(10) .dot {
  animation-delay: calc(var(--rli-animation-duration, 1.2s) / 12 * 3 * -1);
}
.OP-dotted-rli-bounding-box .OP-dotted-indicator .dot-shape-holder:nth-of-type(11) {
  transform: rotate(300deg);
}
.OP-dotted-rli-bounding-box .OP-dotted-indicator .dot-shape-holder:nth-of-type(11) .dot {
  animation-delay: calc(var(--rli-animation-duration, 1.2s) / 12 * 2 * -1);
}
.OP-dotted-rli-bounding-box .OP-dotted-indicator .dot-shape-holder:nth-of-type(12) {
  transform: rotate(330deg);
}
.OP-dotted-rli-bounding-box .OP-dotted-indicator .dot-shape-holder:nth-of-type(12) .dot {
  animation-delay: calc(var(--rli-animation-duration, 1.2s) / 12 * 1 * -1);
}

@property --OP-dotted-phase1-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --OP-dotted-phase2-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --OP-dotted-phase3-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --OP-dotted-phase4-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --rli-animation-duration {
  syntax: "<time>";
  inherits: true;
  initial-value: 1.2s;
}
@keyframes u1qz6qy {
  0%, 39%, 100% {
    opacity: 0;
  }
  40% {
    opacity: 1;
  }
}
@keyframes u1qz6s0 {
  100%, 0% {
    background-color: var(--OP-dotted-phase1-color);
  }
  22% {
    background-color: var(--OP-dotted-phase1-color);
  }
  25% {
    background-color: var(--OP-dotted-phase2-color, var(--OP-dotted-phase1-color));
  }
  47% {
    background-color: var(--OP-dotted-phase2-color, var(--OP-dotted-phase1-color));
  }
  50% {
    background-color: var(--OP-dotted-phase3-color, var(--OP-dotted-phase1-color));
  }
  72% {
    background-color: var(--OP-dotted-phase3-color, var(--OP-dotted-phase1-color));
  }
  75% {
    background-color: var(--OP-dotted-phase4-color, var(--OP-dotted-phase1-color));
  }
  97% {
    background-color: var(--OP-dotted-phase4-color, var(--OP-dotted-phase1-color));
  }
}`);var Bl=Os(typeof window=="object"&&window)||Os(typeof self=="object"&&self)||Os(typeof global=="object"&&global)||function(){return this}()||Function("return this")();function Cp(){var a,r;return!((a=Bl==null?void 0:Bl.crypto)===null||a===void 0)&&a.randomUUID?Bl.crypto.randomUUID():!((r=Bl==null?void 0:Bl.btoa)===null||r===void 0)&&r.name?Bl.btoa(new Date(Math.ceil(1e13*Math.random())).getTime()+""):Date.now().toString(36)+Math.random().toString(36).substring(0)}var Ro=Array.from({length:4},function(a,r){return"--OP-dotted-phase".concat(r+1,"-color")}),c5=function(a){var r,i=ui(a==null?void 0:a.style,a==null?void 0:a.size),u=i.styles,s=i.fontSize,f=a==null?void 0:a.easing,m=oi(a==null?void 0:a.speedPlus,"1.2s").animationPeriod,v=function(p){var g={},z=Ro.length;if(p instanceof Array){for(var M=si(p,z),w=0;w<M.length&&!(w>=4);w++)g[Ro[w]]=M[w];return g}try{if(typeof p!="string")throw new Error("Color String expected");for(var A=0;A<z;A++)g[Ro[A]]=p}catch(E){for(E instanceof Error?console.warn("[".concat(E.message,']: Received "').concat(typeof p,'" with value, ').concat(JSON.stringify(p))):console.warn("".concat(JSON.stringify(p),' received in <OrbitProgress variant="dotted" /> indicator cannot be processed. Using default instead!')),A=0;A<z;A++)g[Ro[A]]=Gn}return g}((r=a==null?void 0:a.color)!==null&&r!==void 0?r:""),h=a!=null&&a.dense?16:12;return X.createElement("span",{className:"rli-d-i-b OP-dotted-rli-bounding-box",style:Ae(Ae(Ae(Ae(Ae({},s&&{fontSize:s}),m&&{"--rli-animation-duration":m}),f&&{"--rli-animation-function":f}),v),u),role:"status","aria-live":"polite","aria-label":"Loading"},X.createElement("span",{className:"rli-d-i-b OP-dotted-indicator"},Array.from({length:h}).map(function(p,g){var z=function(A,E,R){if(E===16){var q=360*A/E,N=E-A,V=Number.parseFloat(R)/E*N*-1;return{transform:"rotate(".concat(q,"deg)"),animationDelay:"".concat(V,"s")}}return{transform:"",animationDelay:""}}(g,h,m),M=z.animationDelay,w=z.transform;return X.createElement("span",{key:Cp(),className:"rli-d-i-b dot-shape-holder",style:w?{transform:w}:void 0},X.createElement("span",{className:"dot",style:M?{animationDelay:M}:void 0}))}),X.createElement(ci,{className:"OP-dotted-text",text:a==null?void 0:a.text,textColor:a==null?void 0:a.textColor})))};ut(`.OP-spokes-rli-bounding-box {
  --OP-spokes-phase1-color: rgb(50, 205, 50);
  font-size: 16px;
  position: relative;
  color: var(--OP-spokes-phase1-color);
}
.OP-spokes-rli-bounding-box .OP-spokes-indicator {
  width: 4.8em;
  height: 4.8em;
  display: block;
  position: relative;
}
.OP-spokes-rli-bounding-box .OP-spokes-indicator .spoke {
  position: absolute;
  height: 1.2em;
  width: 0.4em;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  margin: auto auto auto 50%;
  background-color: var(--OP-spokes-phase1-color);
  border-radius: 0.24em;
  opacity: 0;
  animation: var(--rli-animation-duration, 1.2s) var(--rli-animation-function, ease-in-out) backwards infinite u1qz6sz, calc(var(--rli-animation-duration, 1.2s) * 4) var(--rli-animation-function, ease-in-out) infinite u1qz6t3;
  transform-origin: left center;
}
.OP-spokes-rli-bounding-box .OP-spokes-indicator .spoke:nth-of-type(1) {
  transform: rotate(calc(0 * 360deg / 12)) translate(-50%, -1.56em);
  animation-delay: calc(11 * var(--rli-animation-duration, 1.2s) / 12 * -1);
}
.OP-spokes-rli-bounding-box .OP-spokes-indicator .spoke:nth-of-type(2) {
  transform: rotate(calc(1 * 360deg / 12)) translate(-50%, -1.56em);
  animation-delay: calc(10 * var(--rli-animation-duration, 1.2s) / 12 * -1);
}
.OP-spokes-rli-bounding-box .OP-spokes-indicator .spoke:nth-of-type(3) {
  transform: rotate(calc(2 * 360deg / 12)) translate(-50%, -1.56em);
  animation-delay: calc(9 * var(--rli-animation-duration, 1.2s) / 12 * -1);
}
.OP-spokes-rli-bounding-box .OP-spokes-indicator .spoke:nth-of-type(4) {
  transform: rotate(calc(3 * 360deg / 12)) translate(-50%, -1.56em);
  animation-delay: calc(8 * var(--rli-animation-duration, 1.2s) / 12 * -1);
}
.OP-spokes-rli-bounding-box .OP-spokes-indicator .spoke:nth-of-type(5) {
  transform: rotate(calc(4 * 360deg / 12)) translate(-50%, -1.56em);
  animation-delay: calc(7 * var(--rli-animation-duration, 1.2s) / 12 * -1);
}
.OP-spokes-rli-bounding-box .OP-spokes-indicator .spoke:nth-of-type(6) {
  transform: rotate(calc(5 * 360deg / 12)) translate(-50%, -1.56em);
  animation-delay: calc(6 * var(--rli-animation-duration, 1.2s) / 12 * -1);
}
.OP-spokes-rli-bounding-box .OP-spokes-indicator .spoke:nth-of-type(7) {
  transform: rotate(calc(6 * 360deg / 12)) translate(-50%, -1.56em);
  animation-delay: calc(5 * var(--rli-animation-duration, 1.2s) / 12 * -1);
}
.OP-spokes-rli-bounding-box .OP-spokes-indicator .spoke:nth-of-type(8) {
  transform: rotate(calc(7 * 360deg / 12)) translate(-50%, -1.56em);
  animation-delay: calc(4 * var(--rli-animation-duration, 1.2s) / 12 * -1);
}
.OP-spokes-rli-bounding-box .OP-spokes-indicator .spoke:nth-of-type(9) {
  transform: rotate(calc(8 * 360deg / 12)) translate(-50%, -1.56em);
  animation-delay: calc(3 * var(--rli-animation-duration, 1.2s) / 12 * -1);
}
.OP-spokes-rli-bounding-box .OP-spokes-indicator .spoke:nth-of-type(10) {
  transform: rotate(calc(9 * 360deg / 12)) translate(-50%, -1.56em);
  animation-delay: calc(2 * var(--rli-animation-duration, 1.2s) / 12 * -1);
}
.OP-spokes-rli-bounding-box .OP-spokes-indicator .spoke:nth-of-type(11) {
  transform: rotate(calc(10 * 360deg / 12)) translate(-50%, -1.56em);
  animation-delay: calc(1 * var(--rli-animation-duration, 1.2s) / 12 * -1);
}
.OP-spokes-rli-bounding-box .OP-spokes-indicator .spoke:nth-of-type(12) {
  transform: rotate(calc(11 * 360deg / 12)) translate(-50%, -1.56em);
  animation-delay: calc(0 * var(--rli-animation-duration, 1.2s) / 12 * -1);
}
.OP-spokes-rli-bounding-box .OP-spokes-indicator-text {
  mix-blend-mode: difference;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  color: var(--OP-spokes-phase1-color);
  z-index: -2;
}

@property --OP-spokes-phase1-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --OP-spokes-phase2-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --OP-spokes-phase3-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --OP-spokes-phase4-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --rli-animation-duration {
  syntax: "<time>";
  inherits: true;
  initial-value: 1.2s;
}
@keyframes u1qz6sz {
  from {
    opacity: 1;
  }
  to {
    opacity: 0;
  }
}
@keyframes u1qz6t3 {
  100%, 0% {
    background-color: var(--OP-spokes-phase1-color);
  }
  22% {
    background-color: var(--OP-spokes-phase1-color);
  }
  25% {
    background-color: var(--OP-spokes-phase2-color, var(--OP-spokes-phase1-color));
  }
  42% {
    background-color: var(--OP-spokes-phase2-color, var(--OP-spokes-phase1-color));
  }
  50% {
    background-color: var(--OP-spokes-phase3-color, var(--OP-spokes-phase1-color));
  }
  72% {
    background-color: var(--OP-spokes-phase3-color, var(--OP-spokes-phase1-color));
  }
  75% {
    background-color: var(--OP-spokes-phase4-color, var(--OP-spokes-phase1-color));
  }
  97% {
    background-color: var(--OP-spokes-phase4-color, var(--OP-spokes-phase1-color));
  }
}`);var Mo=Array.from({length:4},function(a,r){return"--OP-spokes-phase".concat(r+1,"-color")}),s5=function(a){var r,i=ui(a==null?void 0:a.style,a==null?void 0:a.size),u=i.styles,s=i.fontSize,f=a==null?void 0:a.easing,m=oi(a==null?void 0:a.speedPlus,"1.2s").animationPeriod,v=function(p){var g={},z=Mo.length;if(p instanceof Array){for(var M=si(p,z),w=0;w<M.length&&!(w>=4);w++)g[Mo[w]]=M[w];return g}try{if(typeof p!="string")throw new Error("Color String expected");for(var A=0;A<z;A++)g[Mo[A]]=p}catch(E){for(E instanceof Error?console.warn("[".concat(E.message,']: Received "').concat(typeof p,'" instead with value, ').concat(JSON.stringify(p))):console.warn("".concat(JSON.stringify(p),' received in <OrbitProgress variant="spokes" /> indicator cannot be processed. Using default instead!')),A=0;A<z;A++)g[Mo[A]]=Gn}return g}((r=a==null?void 0:a.color)!==null&&r!==void 0?r:""),h=a!=null&&a.dense?16:12;return X.createElement("span",{className:"rli-d-i-b OP-spokes-rli-bounding-box",style:Ae(Ae(Ae(Ae(Ae({},s&&{fontSize:s}),m&&{"--rli-animation-duration":m}),f&&{"--rli-animation-function":f}),v),u),role:"status","aria-live":"polite","aria-label":"Loading"},X.createElement("span",{className:"rli-d-i-b OP-spokes-indicator"},Array.from({length:h},function(p,g){return X.createElement("span",{key:Cp(),className:"rli-d-i-b spoke",style:f5(g,h,m)})})),X.createElement(ci,{text:a==null?void 0:a.text,textColor:a==null?void 0:a.textColor}))};function f5(a,r,i){if(r===16){var u=r-a,s=Number.parseFloat(i)/r;return{transform:"rotate(".concat(360*a/r,"deg) translate(-50%, ").concat("-1.56em",")"),animationDelay:"".concat((u-1)*s*-1,"s")}}}ut(`.OP-annulus-dual-sectors-rli-bounding-box {
  --OP-annulus-dual-sectors-phase1-color: rgb(50, 205, 50);
  font-size: 16px;
  box-sizing: border-box;
  display: inline-block;
}
.OP-annulus-dual-sectors-rli-bounding-box .OP-annulus-dual-sectors-indicator {
  width: 5em;
  height: 5em;
  display: inline-block;
  position: relative;
  z-index: 0;
  color: var(--OP-annulus-dual-sectors-phase1-color);
}
.OP-annulus-dual-sectors-rli-bounding-box .OP-annulus-dual-sectors-indicator .annulus-sectors {
  box-sizing: border-box;
  width: 100%;
  height: 100%;
  border-radius: 50%;
  border-width: 0.34em;
  border-style: solid;
  border-color: var(--OP-annulus-dual-sectors-phase1-color) transparent var(--OP-annulus-dual-sectors-phase1-color) transparent;
  background-color: transparent;
  animation: var(--rli-animation-duration, 1.2s) var(--rli-animation-function, linear) infinite u1qz6t5, calc(var(--rli-animation-duration, 1.2s) * 4) var(--rli-animation-function, linear) infinite u1qz6uw;
}
.OP-annulus-dual-sectors-rli-bounding-box .OP-annulus-dual-sectors-indicator .OP-annulus-dual-sectors-text {
  mix-blend-mode: difference;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  z-index: -2;
}

@property --OP-annulus-dual-sectors-phase1-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --OP-annulus-dual-sectors-phase2-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --OP-annulus-dual-sectors-phase3-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --OP-annulus-dual-sectors-phase4-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --rli-animation-duration {
  syntax: "<time>";
  inherits: true;
  initial-value: 1.2s;
}
@keyframes u1qz6t5 {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}
@keyframes u1qz6uw {
  100%, 0% {
    border-color: var(--OP-annulus-dual-sectors-phase1-color) transparent;
  }
  20% {
    border-color: var(--OP-annulus-dual-sectors-phase1-color) transparent;
  }
  25% {
    border-color: var(--OP-annulus-dual-sectors-phase2-color, var(--OP-annulus-dual-sectors-phase1-color)) transparent;
  }
  45% {
    border-color: var(--OP-annulus-dual-sectors-phase2-color, var(--OP-annulus-dual-sectors-phase1-color)) transparent;
  }
  50% {
    border-color: var(--OP-annulus-dual-sectors-phase3-color, var(--OP-annulus-dual-sectors-phase1-color)) transparent;
  }
  70% {
    border-color: var(--OP-annulus-dual-sectors-phase3-color, var(--OP-annulus-dual-sectors-phase1-color)) transparent;
  }
  75% {
    border-color: var(--OP-annulus-dual-sectors-phase4-color, var(--OP-annulus-dual-sectors-phase1-color)) transparent;
  }
  95% {
    border-color: var(--OP-annulus-dual-sectors-phase4-color, var(--OP-annulus-dual-sectors-phase1-color)) transparent;
  }
}`);var Co=Array.from({length:4},function(a,r){return"--OP-annulus-dual-sectors-phase".concat(r+1,"-color")}),d5=function(a){var r,i=ui(a==null?void 0:a.style,a==null?void 0:a.size),u=i.styles,s=i.fontSize,f=a==null?void 0:a.easing,m=oi(a==null?void 0:a.speedPlus,"1.2s").animationPeriod,v=function(p){var g={},z=Co.length;if(p instanceof Array){for(var M=si(p,z),w=0;w<M.length&&!(w>=4);w++)g[Co[w]]=M[w];return g}try{if(typeof p!="string")throw new Error("Color String expected");for(var A=0;A<z;A++)g[Co[A]]=p}catch(E){for(E instanceof Error?console.warn("[".concat(E.message,']: Received "').concat(typeof p,'" with value, ').concat(JSON.stringify(p))):console.warn("".concat(JSON.stringify(p),' received in <OrbitProgress variant="annulus-splits" /> indicator cannot be processed. Using default instead!')),A=0;A<z;A++)g[Co[A]]=Gn}return g}((r=a==null?void 0:a.color)!==null&&r!==void 0?r:""),h=a.dense?"0.45em":"";return X.createElement("span",{className:"rli-d-i-b OP-annulus-dual-sectors-rli-bounding-box",style:Ae(Ae(Ae(Ae(Ae({},s&&{fontSize:s}),m&&{"--rli-animation-duration":m}),f&&{"--rli-animation-function":f}),v),u),role:"status","aria-live":"polite","aria-label":"Loading"},X.createElement("span",{className:"rli-d-i-b OP-annulus-dual-sectors-indicator"},X.createElement("span",{className:"rli-d-i-b annulus-sectors",style:Ae({},h&&{borderWidth:h})}),X.createElement(ci,{className:"OP-annulus-dual-sectors-text",text:a==null?void 0:a.text,textColor:a==null?void 0:a.textColor})))};ut(`.OP-annulus-sector-track-rli-bounding-box {
  --OP-annulus-track-phase1-color: rgba(50, 205, 50, 0.22);
  --OP-annulus-sector-phase1-color: rgb(50, 205, 50);
  font-size: 16px;
  display: inline-block;
}
.OP-annulus-sector-track-rli-bounding-box .OP-annulus-sector-track-indicator {
  width: 5em;
  height: 5em;
  color: var(--OP-annulus-sector-phase1-color);
  display: inline-block;
  position: relative;
  z-index: 0;
}
.OP-annulus-sector-track-rli-bounding-box .OP-annulus-sector-track-indicator .annulus-track-ring {
  width: 100%;
  height: 100%;
  border-width: 0.34em;
  border-style: solid;
  border-radius: 50%;
  box-sizing: border-box;
  border-color: var(--OP-annulus-track-phase1-color);
  border-top-color: var(--OP-annulus-sector-phase1-color);
  animation: var(--rli-animation-duration, 1s) var(--rli-animation-function, linear) infinite u1qz6tq, calc(var(--rli-animation-duration, 1s) * 4) var(--rli-animation-function, linear) infinite u1qz6v8;
}
.OP-annulus-sector-track-rli-bounding-box .OP-annulus-sector-track-indicator .OP-annulus-sector-text {
  mix-blend-mode: difference;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  z-index: -2;
}

@property --OP-annulus-track-phase1-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgba(50, 205, 50, 0.22);
}
@property --OP-annulus-track-phase2-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgba(50, 205, 50, 0.22);
}
@property --OP-annulus-track-phase3-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgba(50, 205, 50, 0.22);
}
@property --OP-annulus-track-phase4-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgba(50, 205, 50, 0.22);
}
@property --OP-annulus-sector-phase1-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --OP-annulus-sector-phase2-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --OP-annulus-sector-phase3-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --OP-annulus-sector-phase4-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --rli-animation-duration {
  syntax: "<time>";
  inherits: true;
  initial-value: 1s;
}
@keyframes u1qz6tq {
  to {
    transform: rotate(1turn);
  }
}
@keyframes u1qz6v8 {
  100%, 0% {
    border-color: var(--OP-annulus-track-phase1-color);
    border-top-color: var(--OP-annulus-sector-phase1-color);
  }
  18% {
    border-color: var(--OP-annulus-track-phase1-color);
    border-top-color: var(--OP-annulus-sector-phase1-color);
  }
  25% {
    border-color: var(--OP-annulus-track-phase2-color, var(--OP-annulus-track-phase1-color));
    border-top-color: var(--OP-annulus-sector-phase2-color, var(--OP-annulus-sector-phase1-color));
  }
  43% {
    border-color: var(--OP-annulus-track-phase2-color, var(--OP-annulus-track-phase1-color));
    border-top-color: var(--OP-annulus-sector-phase2-color, var(--OP-annulus-sector-phase1-color));
  }
  50% {
    border-color: var(--OP-annulus-track-phase3-color, var(--OP-annulus-track-phase1-color));
    border-top-color: var(--OP-annulus-sector-phase3-color, var(--OP-annulus-sector-phase1-color));
  }
  68% {
    border-color: var(--OP-annulus-track-phase3-color, var(--OP-annulus-track-phase1-color));
    border-top-color: var(--OP-annulus-sector-phase3-color, var(--OP-annulus-sector-phase1-color));
  }
  75% {
    border-color: var(--OP-annulus-track-phase4-color, var(--OP-annulus-track-phase1-color));
    border-top-color: var(--OP-annulus-sector-phase4-color, var(--OP-annulus-sector-phase1-color));
  }
  93% {
    border-color: var(--OP-annulus-track-phase4-color, var(--OP-annulus-track-phase1-color));
    border-top-color: var(--OP-annulus-sector-phase4-color, var(--OP-annulus-sector-phase1-color));
  }
}`);var $r=Array.from({length:4},function(a,r){return["--OP-annulus-track-phase".concat(r+1,"-color"),"--OP-annulus-sector-phase".concat(r+1,"-color")]}),ko=function(a){return a===void 0&&(a=1),.25*a},h5=function(a){var r,i=ui(a==null?void 0:a.style,a==null?void 0:a.size),u=i.styles,s=i.fontSize,f=a==null?void 0:a.easing,m=oi(a==null?void 0:a.speedPlus,"1s").animationPeriod,v=function(p){var g={},z=$r.length;if(p instanceof Array){for(var M=si(p,z),w=0;w<M.length&&!(w>=4);w++){var A=$r[w];try{if(!(q=ae(M[w])).isValid())throw new Error("Invalid Color: ".concat(q.getOriginalInput()));var E=q.setAlpha(ko(q.getAlpha())).toRgbString(),R=M[w];g[A[0]]=E,g[A[1]]=R}catch{R=Gn,E=(q=ae(Gn)).setAlpha(ko(q.getAlpha())).toRgbString(),g[A[0]]=E,g[A[1]]=R}}return g}try{var q=ae(p);if(typeof p!="string")throw new Error("Color String expected");if(!q.isValid())throw new Error("Invalid Color: ".concat(q.getOriginalInput()));R=p,E=q.setAlpha(ko(q.getAlpha())).toRgbString();for(var N=0;N<z;N++)g[(A=$r[N])[0]]=E,g[A[1]]=R}catch(V){for(V instanceof Error?console.warn("[".concat(V.message,']: Received "').concat(typeof p,'" with value, ').concat(JSON.stringify(p))):console.warn("".concat(JSON.stringify(p),' received in <OrbitProgress variant="annulus-track" /> indicator cannot be processed. Using default instead!')),R=Gn,E=(q=ae(Gn)).setAlpha(ko(q.getAlpha())).toRgbString(),N=0;N<$r.length;N++)g[(A=$r[N])[0]]=E,g[A[1]]=R}return g}((r=a==null?void 0:a.color)!==null&&r!==void 0?r:""),h=a.dense?"0.45em":"";return X.createElement("span",{className:"rli-d-i-b OP-annulus-sector-track-rli-bounding-box",style:Ae(Ae(Ae(Ae(Ae({},s&&{fontSize:s}),m&&{"--rli-animation-duration":m}),f&&{"--rli-animation-function":f}),v),u),role:"status","aria-live":"polite","aria-label":"Loading"},X.createElement("span",{className:"rli-d-i-b OP-annulus-sector-track-indicator"},X.createElement("span",{className:"rli-d-i-b annulus-track-ring",style:Ae({},h&&{borderWidth:h})}),X.createElement(ci,{className:"OP-annulus-sector-text",text:a==null?void 0:a.text,textColor:a==null?void 0:a.textColor})))},ax=function(a){var r=Object(a).variant,i=r===void 0?"disc":r;return i==="dotted"?X.createElement(c5,Ae({},a)):i==="spokes"?X.createElement(s5,Ae({},a)):i==="disc"?X.createElement(u5,Ae({},a)):i==="split-disc"?X.createElement(d5,Ae({},a)):i==="track-disc"?X.createElement(h5,Ae({},a)):null};ut(`.foursquare-rli-bounding-box {
  --four-square-phase1-color: rgb(50, 205, 50);
  font-size: 16px;
  box-sizing: border-box;
  color: var(--four-square-phase1-color);
  display: inline-block;
  overflow: hidden;
}
.foursquare-rli-bounding-box .foursquare-indicator {
  height: 5.3033008589em;
  width: 5.3033008589em;
  position: relative;
  display: block;
}
.foursquare-rli-bounding-box .foursquare-indicator .squares-container {
  position: absolute;
  z-index: 0;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  margin: auto;
  height: 2.5em;
  width: 2.5em;
  color: inherit;
  will-change: color, width, height;
  transform: rotate(45deg);
  animation: var(--rli-animation-duration, 1s) var(--rli-animation-function, cubic-bezier(0.05, 0.28, 0.79, 0.98)) infinite u1qz6cv, calc(var(--rli-animation-duration, 1s) * 4) var(--rli-animation-function, cubic-bezier(0.05, 0.28, 0.79, 0.98)) infinite u1qz6e3;
}
.foursquare-rli-bounding-box .foursquare-indicator .squares-container .square {
  position: absolute;
  width: 1.25em;
  height: 1.25em;
  border-radius: 0.1875em;
  background-color: currentColor;
  animation: u1qz6cr var(--rli-animation-duration, 1s) var(--rli-animation-function, cubic-bezier(0.05, 0.28, 0.79, 0.98)) both infinite;
}
.foursquare-rli-bounding-box .foursquare-indicator .squares-container .square.square1 {
  top: 0;
  left: 0;
}
.foursquare-rli-bounding-box .foursquare-indicator .squares-container .square.square2 {
  top: 0;
  right: 0;
}
.foursquare-rli-bounding-box .foursquare-indicator .squares-container .square.square3 {
  bottom: 0;
  left: 0;
}
.foursquare-rli-bounding-box .foursquare-indicator .squares-container .square.square4 {
  bottom: 0;
  right: 0;
}

@property --four-square-phase1-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --four-square-phase2-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --four-square-phase3-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --four-square-phase4-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --rli-animation-duration {
  syntax: "<time>";
  inherits: true;
  initial-value: 1s;
}
@keyframes u1qz6cv {
  0% {
    width: 2.5em;
    height: 2.5em;
  }
  10% {
    width: 2.5em;
    height: 2.5em;
  }
  50% {
    width: 3.75em;
    height: 3.75em;
  }
  90% {
    width: 2.5em;
    height: 2.5em;
  }
  100% {
    width: 2.5em;
    height: 2.5em;
  }
}
@keyframes u1qz6cr {
  0% {
    transform: rotateZ(0deg);
  }
  10% {
    transform: rotateZ(0deg);
  }
  50% {
    transform: rotateZ(90deg);
  }
  90% {
    transform: rotateZ(90deg);
  }
  100% {
    transform: rotateZ(90deg);
  }
}
@keyframes u1qz6e3 {
  100%, 0% {
    color: var(--four-square-phase1-color);
  }
  20% {
    color: var(--four-square-phase1-color);
  }
  25% {
    color: var(--four-square-phase2-color, var(--four-square-phase1-color));
  }
  45% {
    color: var(--four-square-phase2-color, var(--four-square-phase1-color));
  }
  50% {
    color: var(--four-square-phase3-color, var(--four-square-phase1-color));
  }
  70% {
    color: var(--four-square-phase3-color, var(--four-square-phase1-color));
  }
  75% {
    color: var(--four-square-phase4-color, var(--four-square-phase1-color));
  }
  95% {
    color: var(--four-square-phase4-color, var(--four-square-phase1-color));
  }
}`);Array.from({length:4},function(a,r){return"--four-square-phase".concat(r+1,"-color")});ut(`.mosaic-rli-bounding-box {
  --mosaic-phase1-color: rgb(50, 205, 50);
  box-sizing: border-box;
  font-size: 16px;
  color: var(--mosaic-phase1-color);
}
.mosaic-rli-bounding-box .mosaic-indicator {
  width: 5em;
  height: 5em;
  color: currentColor;
  display: grid;
  gap: 0.125em;
  grid-template-columns: repeat(3, 1fr);
  grid-template-areas: "a b c" "d e f" "g h i";
  position: relative;
  z-index: 0;
}
.mosaic-rli-bounding-box .mosaic-indicator .mosaic-cube-text {
  mix-blend-mode: difference;
  position: absolute;
  top: 105%;
  left: 50%;
  transform: translateX(-50%);
  z-index: -2;
}
.mosaic-rli-bounding-box .mosaic-indicator .mosaic-cube {
  background-color: var(--mosaic-phase1-color);
  animation-name: u1qz6bl, u1qz6c9;
  animation-duration: var(--rli-animation-duration, 1.5s), calc(var(--rli-animation-duration, 1.5s) * 4);
  animation-timing-function: var(--rli-animation-function, ease-in-out);
  animation-iteration-count: infinite;
}
.mosaic-rli-bounding-box .mosaic-indicator .mosaic-cube1 {
  animation-delay: calc(var(--mosaic-skip-interval, 0.1s) * 2);
  grid-area: a;
}
.mosaic-rli-bounding-box .mosaic-indicator .mosaic-cube2 {
  animation-delay: calc(var(--mosaic-skip-interval, 0.1s) * 3);
  grid-area: b;
}
.mosaic-rli-bounding-box .mosaic-indicator .mosaic-cube3 {
  grid-area: c;
  animation-delay: calc(var(--mosaic-skip-interval, 0.1s) * 4);
}
.mosaic-rli-bounding-box .mosaic-indicator .mosaic-cube4 {
  grid-area: d;
  animation-delay: calc(var(--mosaic-skip-interval, 0.1s) * 1);
}
.mosaic-rli-bounding-box .mosaic-indicator .mosaic-cube5 {
  grid-area: e;
  animation-delay: calc(var(--mosaic-skip-interval, 0.1s) * 2);
}
.mosaic-rli-bounding-box .mosaic-indicator .mosaic-cube6 {
  grid-area: f;
  animation-delay: calc(var(--mosaic-skip-interval, 0.1s) * 3);
}
.mosaic-rli-bounding-box .mosaic-indicator .mosaic-cube7 {
  grid-area: g;
  animation-delay: 0s;
}
.mosaic-rli-bounding-box .mosaic-indicator .mosaic-cube8 {
  grid-area: h;
  animation-delay: calc(var(--mosaic-skip-interval, 0.1s) * 1);
}
.mosaic-rli-bounding-box .mosaic-indicator .mosaic-cube9 {
  grid-area: i;
  animation-delay: calc(var(--mosaic-skip-interval, 0.1s) * 2);
}

@property --mosaic-phase1-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --mosaic-phase2-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --mosaic-phase3-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --mosaic-phase4-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --rli-animation-duration {
  syntax: "<time>";
  inherits: true;
  initial-value: 1.5s;
}
@keyframes u1qz6bl {
  0%, 60%, 100% {
    transform: scale3D(1, 1, 1);
  }
  30% {
    transform: scale3D(0, 0, 1);
  }
}
@keyframes u1qz6c9 {
  100%, 0% {
    background-color: var(--mosaic-phase1-color);
  }
  25% {
    background-color: var(--mosaic-phase2-color, var(--mosaic-phase1-color));
  }
  50% {
    background-color: var(--mosaic-phase3-color, var(--mosaic-phase1-color));
  }
  75% {
    background-color: var(--mosaic-phase4-color, var(--mosaic-phase1-color));
  }
}`);Array.from({length:4},function(a,r){return"--mosaic-phase".concat(r+1,"-color")});ut(`.riple-rli-bounding-box {
  --riple-phase1-color: rgb(50, 205, 50);
  box-sizing: border-box;
  font-size: 16px;
  display: inline-block;
  color: var(--riple-phase1-color);
}
.riple-rli-bounding-box .riple-indicator {
  display: inline-block;
  width: 5em;
  height: 5em;
  position: relative;
  z-index: 0;
}
.riple-rli-bounding-box .riple-indicator .riple-text {
  mix-blend-mode: difference;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  z-index: -2;
}
.riple-rli-bounding-box .riple-indicator .riple {
  --border-width: 0.25em;
  position: absolute;
  border: var(--border-width) solid var(--riple-phase1-color);
  opacity: 1;
  border-radius: 50%;
  will-change: top, right, left, bottom, border-color;
  animation: var(--rli-animation-duration, 1s) var(--rli-animation-function, cubic-bezier(0, 0.2, 0.8, 1)) infinite u1qz6mm, calc(var(--rli-animation-duration, 1s) * 4) var(--rli-animation-function, cubic-bezier(0, 0.2, 0.8, 1)) infinite u1qz6og;
}
.riple-rli-bounding-box .riple-indicator .riple:nth-of-type(2) {
  animation-delay: calc(var(--rli-animation-duration, 1s) / 2 * -1);
}

@property --riple-phase1-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --riple-phase2-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --riple-phase3-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --riple-phase4-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --rli-animation-duration {
  syntax: "<time>";
  inherits: true;
  initial-value: 1s;
}
@keyframes u1qz6mm {
  0% {
    top: calc(50% - var(--border-width));
    left: calc(50% - var(--border-width));
    right: calc(50% - var(--border-width));
    bottom: calc(50% - var(--border-width));
    opacity: 0;
  }
  4.9% {
    top: calc(50% - var(--border-width));
    left: calc(50% - var(--border-width));
    right: calc(50% - var(--border-width));
    bottom: calc(50% - var(--border-width));
    opacity: 0;
  }
  5% {
    top: calc(50% - var(--border-width));
    left: calc(50% - var(--border-width));
    right: calc(50% - var(--border-width));
    bottom: calc(50% - var(--border-width));
    opacity: 1;
  }
  100% {
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    opacity: 0;
  }
}
@keyframes u1qz6og {
  100%, 0% {
    border-color: var(--riple-phase1-color);
  }
  24.9% {
    border-color: var(--riple-phase1-color);
  }
  25% {
    border-color: var(--riple-phase2-color, var(--riple-phase1-color));
  }
  49.9% {
    border-color: var(--riple-phase2-color, var(--riple-phase1-color));
  }
  50% {
    border-color: var(--riple-phase3-color, var(--riple-phase1-color));
  }
  74.9% {
    border-color: var(--riple-phase3-color, var(--riple-phase1-color));
  }
  75% {
    border-color: var(--riple-phase4-color, var(--riple-phase1-color));
  }
  99.9% {
    border-color: var(--riple-phase4-color, var(--riple-phase1-color));
  }
}`);Array.from({length:4},function(a,r){return"--riple-phase".concat(r+1,"-color")});ut(`.pulsate-rli-bounding-box {
  --TD-pulsate-phase1-color: rgb(50, 205, 50);
  font-size: 16px;
  display: inline-block;
  box-sizing: border-box;
  color: var(--TD-pulsate-phase1-color);
}
.pulsate-rli-bounding-box .pulsate-indicator {
  width: 4.4em;
  height: 1.1em;
  text-align: center;
  position: relative;
  z-index: 0;
  display: flex;
  justify-content: space-between;
  flex-wrap: nowrap;
  align-items: center;
}
.pulsate-rli-bounding-box .pulsate-indicator .pulsate-dot {
  width: 1.1em;
  height: 1.1em;
  border-radius: 50%;
  background-color: var(--TD-pulsate-phase1-color);
  transform: scale(0);
  animation: var(--rli-animation-duration, 1.2s) var(--rli-animation-function, ease-in-out) var(--delay) infinite u1qz6uj, calc(var(--rli-animation-duration, 1.2s) * 4) var(--rli-animation-function, ease-in-out) var(--delay) infinite u1qz6vi;
}
.pulsate-rli-bounding-box .pulsate-indicator .pulsate-dot:nth-of-type(1) {
  --delay: calc(var(--rli-animation-duration, 1.2s) * 0.15 * -1);
}
.pulsate-rli-bounding-box .pulsate-indicator .pulsate-dot:nth-of-type(2) {
  --delay: calc(var(--rli-animation-duration, 1.2s) * 0);
}
.pulsate-rli-bounding-box .pulsate-indicator .pulsate-dot:nth-of-type(3) {
  --delay: calc(var(--rli-animation-duration, 1.2s) * 0.15);
}
.pulsate-rli-bounding-box .pulsate-text {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  width: 80%;
  text-transform: uppercase;
  text-align: center;
  font-size: 0.6em;
  letter-spacing: 0.5px;
  font-family: sans-serif;
  mix-blend-mode: difference;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  z-index: -2;
}

@property --TD-pulsate-phase1-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --TD-pulsate-phase2-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --TD-pulsate-phase3-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --TD-pulsate-phase4-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --rli-animation-duration {
  syntax: "<time>";
  inherits: true;
  initial-value: 1.2s;
}
@keyframes u1qz6uj {
  0%, 90%, 100% {
    transform: scale(0);
  }
  40% {
    transform: scale(1);
  }
}
@keyframes u1qz6vi {
  0%, 100% {
    background-color: var(--TD-pulsate-phase1-color);
  }
  24.9% {
    background-color: var(--TD-pulsate-phase1-color);
  }
  25% {
    background-color: var(--TD-pulsate-phase2-color, var(--TD-pulsate-phase1-color));
  }
  49.9% {
    background-color: var(--TD-pulsate-phase2-color, var(--TD-pulsate-phase1-color));
  }
  50% {
    background-color: var(--TD-pulsate-phase3-color, var(--TD-pulsate-phase1-color));
  }
  74.9% {
    background-color: var(--TD-pulsate-phase3-color, var(--TD-pulsate-phase1-color));
  }
  75% {
    background-color: var(--TD-pulsate-phase4-color, var(--TD-pulsate-phase1-color));
  }
  99.9% {
    background-color: var(--TD-pulsate-phase4-color, var(--TD-pulsate-phase1-color));
  }
}`);Array.from({length:4},function(a,r){return"--TD-pulsate-phase".concat(r+1,"-color")});ut(`.brick-stack-rli-bounding-box {
  --TD-brick-stack-phase1-color: rgb(50, 205, 50);
  box-sizing: border-box;
  font-size: 16px;
  display: inline-block;
  color: var(--TD-brick-stack-phase1-color);
}
.brick-stack-rli-bounding-box .brick-stack-indicator {
  width: 2.8em;
  height: 2.8em;
  position: relative;
  display: block;
  margin: 0 auto;
}
.brick-stack-rli-bounding-box .brick-stack {
  width: 100%;
  height: 100%;
  background: radial-gradient(circle closest-side, currentColor 0% 95%, rgba(0, 0, 0, 0) calc(95% + 1px)) 0 0/40% 40% no-repeat, radial-gradient(circle closest-side, currentColor 0% 95%, rgba(0, 0, 0, 0) calc(95% + 1px)) 0 100%/40% 40% no-repeat, radial-gradient(circle closest-side, currentColor 0% 95%, rgba(0, 0, 0, 0) calc(95% + 1px)) 100% 100%/40% 40% no-repeat;
  animation: var(--rli-animation-duration, 1s) var(--rli-animation-function, ease-out) infinite u1qz6w1, calc(var(--rli-animation-duration, 1s) * 4) var(--rli-animation-function, ease-out) infinite u1qz6x5;
}

@property --TD-brick-stack-phase1-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --TD-brick-stack-phase2-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --TD-brick-stack-phase3-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --TD-brick-stack-phase4-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --rli-animation-duration {
  syntax: "<time>";
  inherits: true;
  initial-value: 1s;
}
@keyframes u1qz6w1 {
  0% {
    background-position: 0 0, 0 100%, 100% 100%;
  }
  25% {
    background-position: 100% 0, 0 100%, 100% 100%;
  }
  50% {
    background-position: 100% 0, 0 0, 100% 100%;
  }
  75% {
    background-position: 100% 0, 0 0, 0 100%;
  }
  100% {
    background-position: 100% 100%, 0 0, 0 100%;
  }
}
@keyframes u1qz6x5 {
  100%, 0% {
    color: var(--TD-brick-stack-phase1-color);
  }
  20% {
    color: var(--TD-brick-stack-phase1-color);
  }
  25% {
    color: var(--TD-brick-stack-phase2-color, var(--TD-brick-stack-phase1-color));
  }
  45% {
    color: var(--TD-brick-stack-phase2-color, var(--TD-brick-stack-phase1-color));
  }
  50% {
    color: var(--TD-brick-stack-phase3-color, var(--TD-brick-stack-phase1-color));
  }
  70% {
    color: var(--TD-brick-stack-phase3-color, var(--TD-brick-stack-phase1-color));
  }
  75% {
    color: var(--TD-brick-stack-phase4-color, var(--TD-brick-stack-phase1-color));
  }
  95% {
    color: var(--TD-brick-stack-phase4-color, var(--TD-brick-stack-phase1-color));
  }
}`);Array.from({length:4},function(a,r){return"--TD-brick-stack-phase".concat(r+1,"-color")});ut(`.bob-rli-bounding-box {
  --TD-bob-phase1-color: rgb(50, 205, 50);
  box-sizing: border-box;
  font-size: 16px;
  display: inline-block;
  color: var(--TD-bob-phase1-color);
}
.bob-rli-bounding-box .bob-indicator {
  width: 4.4em;
  height: 2.2em;
  position: relative;
  display: block;
  margin: 0 auto;
}
.bob-rli-bounding-box .bob-indicator .bobbing,
.bob-rli-bounding-box .bob-indicator .bobbing::before,
.bob-rli-bounding-box .bob-indicator .bobbing::after {
  width: 1.1em;
  height: 100%;
  display: grid;
  animation: var(--rli-animation-duration, 1.2s) var(--rli-animation-function, linear) var(--delay) infinite u1qz6wd, calc(var(--rli-animation-duration, 1.2s) * 4) var(--rli-animation-function, linear) var(--delay) infinite u1qz6xx;
}
.bob-rli-bounding-box .bob-indicator .bobbing::before,
.bob-rli-bounding-box .bob-indicator .bobbing::after {
  content: "";
  grid-area: 1/1;
}
.bob-rli-bounding-box .bob-indicator .bobbing {
  --delay: calc(var(--rli-animation-duration, 1.2s) * 0.12 * -1);
  background: radial-gradient(circle closest-side at center, currentColor 0% 92%, rgba(0, 0, 0, 0) calc(92% + 1px)) 50% 50%/100% 50% no-repeat;
}
.bob-rli-bounding-box .bob-indicator .bobbing::before {
  --delay: calc(var(--rli-animation-duration, 1.2s) * 0);
  transform: translateX(150%);
  background: radial-gradient(circle closest-side at center, currentColor 0% 92%, rgba(0, 0, 0, 0) calc(92% + 1px)) 50% 50%/100% 50% no-repeat;
}
.bob-rli-bounding-box .bob-indicator .bobbing::after {
  --delay: calc(var(--rli-animation-duration, 1.2s) * 0.12);
  transform: translateX(300%);
  background: radial-gradient(circle closest-side at center, currentColor 0% 92%, rgba(0, 0, 0, 0) calc(92% + 1px)) 50% 50%/100% 50% no-repeat;
}

@property --TD-bob-phase1-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --TD-bob-phase2-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --TD-bob-phase3-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --TD-bob-phase4-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --rli-animation-duration {
  syntax: "<time>";
  inherits: true;
  initial-value: 1.2s;
}
@keyframes u1qz6wd {
  100%, 0% {
    background-position: 50% 50%;
  }
  15% {
    background-position: 50% 10%;
  }
  30% {
    background-position: 50% 100%;
  }
  40% {
    background-position: 50% 0%;
  }
  50% {
    background-position: 50% 90%;
  }
  70% {
    background-position: 50% 10%;
  }
  98% {
    background-position: 50% 50%;
  }
}
@keyframes u1qz6xx {
  100%, 0% {
    color: var(--TD-bob-phase1-color);
  }
  22% {
    color: var(--TD-bob-phase1-color);
  }
  25% {
    color: var(--TD-bob-phase2-color, var(--TD-bob-phase1-color));
  }
  47% {
    color: var(--TD-bob-phase2-color, var(--TD-bob-phase1-color));
  }
  50% {
    color: var(--TD-bob-phase3-color, var(--TD-bob-phase1-color));
  }
  72% {
    color: var(--TD-bob-phase3-color, var(--TD-bob-phase1-color));
  }
  75% {
    color: var(--TD-bob-phase4-color, var(--TD-bob-phase1-color));
  }
  97% {
    color: var(--TD-bob-phase4-color, var(--TD-bob-phase1-color));
  }
}`);Array.from({length:4},function(a,r){return"--TD-bob-phase".concat(r+1,"-color")});ut(`.bounce-rli-bounding-box {
  --TD-bounce-phase1-color: rgb(50, 205, 50);
  font-size: 16px;
  color: var(--TD-bounce-phase1-color);
  display: inline-block;
  padding-bottom: 0.25125em;
}
.bounce-rli-bounding-box .wrapper {
  --dot1-delay: 0s;
  --dot1-x-offset: 0.55em;
  --dot2-delay: calc((var(--rli-animation-duration, 0.5s) + var(--rli-animation-duration, 0.5s) * 0.75) * -1);
  --dot2-x-offset: 2.2em;
  --dot3-delay: calc((var(--rli-animation-duration, 0.5s) + var(--rli-animation-duration, 0.5s) * 0.5) * -1);
  --dot3-x-offset: 3.85em;
  width: 5.5em;
  height: 3.125em;
  position: relative;
  display: block;
  margin: 0 auto;
}
.bounce-rli-bounding-box .wrapper .group {
  display: block;
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
}
.bounce-rli-bounding-box .wrapper .group .dot {
  width: 1.1em;
  height: 1.1em;
  position: absolute;
  border-radius: 50%;
  background-color: var(--TD-bounce-phase1-color);
  transform-origin: 50%;
  animation: var(--rli-animation-duration, 0.5s) var(--rli-animation-function, cubic-bezier(0.74, 0.1, 0.74, 1)) alternate infinite u1qz6yl, calc(var(--rli-animation-duration, 0.5s) * 4) var(--rli-animation-function, cubic-bezier(0.74, 0.1, 0.74, 1)) infinite u1qz6zs;
}
.bounce-rli-bounding-box .wrapper .group .dot:nth-of-type(1) {
  left: var(--dot1-x-offset);
  animation-delay: var(--dot1-delay), 0s;
}
.bounce-rli-bounding-box .wrapper .group .dot:nth-of-type(2) {
  left: var(--dot2-x-offset);
  animation-delay: var(--dot2-delay), 0s;
}
.bounce-rli-bounding-box .wrapper .group .dot:nth-of-type(3) {
  left: var(--dot3-x-offset);
  animation-delay: var(--dot3-delay), 0s;
}
.bounce-rli-bounding-box .wrapper .group .shadow {
  width: 1.1em;
  height: 0.22em;
  border-radius: 50%;
  background-color: rgba(0, 0, 0, 0.5);
  position: absolute;
  top: 101%;
  transform-origin: 50%;
  z-index: -1;
  filter: blur(1px);
  animation: var(--rli-animation-duration, 0.5s) var(--rli-animation-function, cubic-bezier(0.74, 0.1, 0.74, 1)) alternate infinite u1qz6z4;
}
.bounce-rli-bounding-box .wrapper .group .shadow:nth-of-type(1) {
  left: var(--dot1-x-offset);
  animation-delay: var(--dot1-delay);
}
.bounce-rli-bounding-box .wrapper .group .shadow:nth-of-type(2) {
  left: var(--dot2-x-offset);
  animation-delay: var(--dot2-delay);
}
.bounce-rli-bounding-box .wrapper .group .shadow:nth-of-type(3) {
  left: var(--dot3-x-offset);
  animation-delay: var(--dot3-delay);
}

@property --TD-bounce-phase1-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --TD-bounce-phase2-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --TD-bounce-phase3-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --TD-bounce-phase4-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --rli-animation-duration {
  syntax: "<time>";
  inherits: true;
  initial-value: 0.5s;
}
@keyframes u1qz6yl {
  0% {
    top: 0%;
  }
  60% {
    height: 1.25em;
    border-radius: 50%;
    transform: scaleX(1);
  }
  100% {
    top: 100%;
    height: 0.22em;
    transform: scaleX(1.5);
    filter: blur(0.4px);
  }
}
@keyframes u1qz6z4 {
  0% {
    transform: scaleX(0.2);
    opacity: 0.2;
  }
  60% {
    opacity: 0.4;
  }
  100% {
    transform: scaleX(1.5);
    opacity: 0.6;
  }
}
@keyframes u1qz6zs {
  0%, 100% {
    background-color: var(--TD-bounce-phase1-color);
  }
  20% {
    background-color: var(--TD-bounce-phase1-color);
  }
  25% {
    background-color: var(--TD-bounce-phase2-color, var(--TD-bounce-phase1-color));
  }
  45% {
    background-color: var(--TD-bounce-phase2-color, var(--TD-bounce-phase1-color));
  }
  50% {
    background-color: var(--TD-bounce-phase3-color, var(--TD-bounce-phase1-color));
  }
  70% {
    background-color: var(--TD-bounce-phase3-color, var(--TD-bounce-phase1-color));
  }
  75% {
    background-color: var(--TD-bounce-phase4-color, var(--TD-bounce-phase1-color));
  }
  95% {
    background-color: var(--TD-bounce-phase4-color, var(--TD-bounce-phase1-color));
  }
}`);Array.from({length:4},function(a,r){return"--TD-bounce-phase".concat(r+1,"-color")});ut(`.blink-blur-rli-bounding-box {
  --shape-phase1-color: rgb(50, 205, 50);
  font-size: 16px;
  color: var(--shape-phase1-color);
}
.blink-blur-rli-bounding-box .blink-blur-indicator {
  isolation: isolate;
  display: flex;
  flex-direction: row;
  -moz-column-gap: 0.4em;
       column-gap: 0.4em;
}
.blink-blur-rli-bounding-box .blink-blur-indicator .blink-blur-shape {
  --x-deg: -20deg;
  width: 1.8em;
  height: 2.25em;
  border-radius: 0.25em;
  color: inherit;
  transform: skewX(var(--x-deg));
  background-color: var(--shape-phase1-color);
  animation-name: u1qz6i2, u1qz6js;
  animation-duration: var(--rli-animation-duration, 1.2s), calc(var(--rli-animation-duration, 1.2s) * 4);
  animation-timing-function: var(--rli-animation-function, ease-in);
  animation-iteration-count: infinite;
}
.blink-blur-rli-bounding-box .blink-blur-indicator .blink-blur-shape.blink-blur-shape1 {
  animation-delay: calc(var(--rli-animation-duration, 1.2s) * 0.5 * -1);
}
.blink-blur-rli-bounding-box .blink-blur-indicator .blink-blur-shape.blink-blur-shape2 {
  animation-delay: calc(var(--rli-animation-duration, 1.2s) * 0.4 * -1);
}
.blink-blur-rli-bounding-box .blink-blur-indicator .blink-blur-shape.blink-blur-shape3 {
  animation-delay: calc(var(--rli-animation-duration, 1.2s) * 0.3 * -1);
}
.blink-blur-rli-bounding-box .blink-blur-indicator .blink-blur-shape.blink-blur-shape4 {
  animation-delay: calc(var(--rli-animation-duration, 1.2s) * 0.2 * -1);
}
.blink-blur-rli-bounding-box .blink-blur-indicator .blink-blur-shape.blink-blur-shape5 {
  animation-delay: calc(var(--rli-animation-duration, 1.2s) * 0.1 * -1);
}

@property --shape-phase1-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --shape-phase2-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --shape-phase3-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --shape-phase4-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --rli-animation-duration {
  syntax: "<time>";
  inherits: true;
  initial-value: 1.2s;
}
@keyframes u1qz6i2 {
  100%, 0% {
    opacity: 0.3;
    filter: blur(0.0675em) drop-shadow(0 0 0.0625em);
    transform: skewX(var(--x-deg)) scale(1.2, 1.45);
  }
  39% {
    opacity: 0.8;
  }
  40%, 41%, 42% {
    opacity: 0;
  }
  43% {
    opacity: 0.8;
  }
  50% {
    opacity: 1;
    filter: blur(0em) drop-shadow(0 0 0em);
    transform: skewX(var(--x-deg)) scale(1, 1);
  }
}
@keyframes u1qz6js {
  100%, 0% {
    color: var(--shape-phase1-color);
    background-color: var(--shape-phase1-color);
  }
  25% {
    color: var(--shape-phase2-color, var(--shape-phase1-color));
    background-color: var(--shape-phase2-color, var(--shape-phase1-color));
  }
  50% {
    color: var(--shape-phase3-color, var(--shape-phase1-color));
    background-color: var(--shape-phase3-color, var(--shape-phase1-color));
  }
  75% {
    color: var(--shape-phase4-color, var(--shape-phase1-color));
    background-color: var(--shape-phase4-color, var(--shape-phase1-color));
  }
}`);Array.from({length:4},function(a,r){return"--shape-phase".concat(r+1,"-color")});ut(`.trophy-spin-rli-bounding-box {
  --trophySpin-phase1-color: rgb(50, 205, 50);
  box-sizing: border-box;
  font-size: 16px;
  position: relative;
  isolation: isolate;
  color: var(--trophySpin-phase1-color);
}
.trophy-spin-rli-bounding-box .trophy-spin-indicator {
  width: 4em;
  perspective: 1000px;
  transform-style: preserve-3d;
  display: block;
  margin: 0 auto;
}
.trophy-spin-rli-bounding-box .trophy-spin-indicator .blade {
  display: block;
  width: 4em;
  height: 0.5em;
  background: var(--trophySpin-phase1-color);
  animation: u1qz6nk var(--rli-animation-duration, 2.5s) var(--rli-animation-function, linear) infinite, u1qz6op calc(var(--rli-animation-duration, 2.5s) * 0.5) var(--rli-animation-function, linear) infinite, u1qz6pg calc(var(--rli-animation-duration, 2.5s) * 4) var(--rli-animation-function, linear) infinite;
}
.trophy-spin-rli-bounding-box .trophy-spin-indicator .blade:nth-of-type(8) {
  animation-delay: calc(var(--rli-animation-duration, 2.5s) / 2 / 8 * 0 * -1);
}
.trophy-spin-rli-bounding-box .trophy-spin-indicator .blade:nth-of-type(7) {
  animation-delay: calc(var(--rli-animation-duration, 2.5s) / 2 / 8 * 1 * -1);
}
.trophy-spin-rli-bounding-box .trophy-spin-indicator .blade:nth-of-type(6) {
  animation-delay: calc(var(--rli-animation-duration, 2.5s) / 2 / 8 * 2 * -1);
}
.trophy-spin-rli-bounding-box .trophy-spin-indicator .blade:nth-of-type(5) {
  animation-delay: calc(var(--rli-animation-duration, 2.5s) / 2 / 8 * 3 * -1);
}
.trophy-spin-rli-bounding-box .trophy-spin-indicator .blade:nth-of-type(4) {
  animation-delay: calc(var(--rli-animation-duration, 2.5s) / 2 / 8 * 4 * -1);
}
.trophy-spin-rli-bounding-box .trophy-spin-indicator .blade:nth-of-type(3) {
  animation-delay: calc(var(--rli-animation-duration, 2.5s) / 2 / 8 * 5 * -1);
}
.trophy-spin-rli-bounding-box .trophy-spin-indicator .blade:nth-of-type(2) {
  animation-delay: calc(var(--rli-animation-duration, 2.5s) / 2 / 8 * 6 * -1);
}
.trophy-spin-rli-bounding-box .trophy-spin-indicator .blade:nth-of-type(1) {
  animation-delay: calc(var(--rli-animation-duration, 2.5s) / 2 / 8 * 7 * -1);
}

@property --trophySpin-phase1-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --trophySpin-phase2-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --trophySpin-phase3-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --trophySpin-phase4-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --rli-animation-duration {
  syntax: "<time>";
  inherits: true;
  initial-value: 2.5s;
}
@keyframes u1qz6nk {
  to {
    transform: rotateY(1turn) rotateX(-25deg);
  }
}
@keyframes u1qz6op {
  100%, 0% {
    filter: brightness(1);
    opacity: 1;
  }
  15% {
    filter: brightness(1);
  }
  25% {
    opacity: 0.96;
  }
  30% {
    filter: brightness(0.92);
  }
  50% {
    filter: brightness(0.7);
    opacity: 1;
  }
  75% {
    filter: brightness(0.92);
    opacity: 0.96;
  }
  90% {
    filter: brightness(1);
  }
}
@keyframes u1qz6pg {
  100%, 0% {
    background-color: var(--trophySpin-phase1-color);
  }
  18% {
    background-color: var(--trophySpin-phase1-color);
  }
  25% {
    background-color: var(--trophySpin-phase2-color, var(--trophySpin-phase1-color));
  }
  43% {
    background-color: var(--trophySpin-phase2-color, var(--trophySpin-phase1-color));
  }
  50% {
    background-color: var(--trophySpin-phase3-color, var(--trophySpin-phase1-color));
  }
  68% {
    background-color: var(--trophySpin-phase3-color, var(--trophySpin-phase1-color));
  }
  75% {
    background-color: var(--trophySpin-phase4-color, var(--trophySpin-phase1-color));
  }
  93% {
    background-color: var(--trophySpin-phase4-color, var(--trophySpin-phase1-color));
  }
}`);Array.from({length:4},function(a,r){return"--trophySpin-phase".concat(r+1,"-color")});ut(`.slab-rli-bounding-box {
  --slab-phase1-color: rgb(50, 205, 50);
  font-size: 16px;
  color: var(--slab-phase1-color);
  position: relative;
}
.slab-rli-bounding-box .slab-indicator {
  position: relative;
  display: block;
  width: 7em;
  height: 4em;
  margin: 0 auto;
  overflow: hidden;
}
.slab-rli-bounding-box .slab-indicator .slabs-wrapper {
  width: 4em;
  height: 4em;
  transform: perspective(15em) rotateX(66deg) rotateZ(-25deg);
  transform-style: preserve-3d;
  transform-origin: 50% 100%;
  display: block;
  position: absolute;
  bottom: 0;
  right: 0;
}
.slab-rli-bounding-box .slab-indicator .slabs-wrapper .slab {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: var(--slab-phase1-color);
  opacity: 0;
  box-shadow: -0.08em 0.15em 0 rgba(0, 0, 0, 0.45);
  transform-origin: 0% 0%;
  animation: calc(var(--rli-animation-duration-unitless, 3) * 1s) var(--rli-animation-function, linear) infinite u1qz6km, calc(var(--rli-animation-duration-unitless, 3) * 4s) var(--rli-animation-function, linear) infinite u1qz6lk;
}
.slab-rli-bounding-box .slab-indicator .slabs-wrapper .slab:nth-child(1) {
  animation-delay: calc(4 / (16 / var(--rli-animation-duration-unitless, 3)) * 3 * -1 * 1s);
}
.slab-rli-bounding-box .slab-indicator .slabs-wrapper .slab:nth-child(2) {
  animation-delay: calc(4 / (16 / var(--rli-animation-duration-unitless, 3)) * 2 * -1 * 1s);
}
.slab-rli-bounding-box .slab-indicator .slabs-wrapper .slab:nth-child(3) {
  animation-delay: calc(4 / (16 / var(--rli-animation-duration-unitless, 3)) * -1 * 1s);
}
.slab-rli-bounding-box .slab-indicator .slabs-wrapper .slab:nth-child(4) {
  animation-delay: 0s;
}

@property --slab-phase1-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --slab-phase2-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --slab-phase3-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --slab-phase4-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --rli-animation-duration-unitless {
  syntax: "<number>";
  inherits: true;
  initial-value: 3;
}
@keyframes u1qz6km {
  0% {
    transform: translateY(0) rotateX(30deg);
    opacity: 0;
  }
  10% {
    transform: translateY(-40%) rotateX(0deg);
    opacity: 1;
  }
  25% {
    opacity: 1;
  }
  100% {
    transform: translateY(-400%) rotateX(0deg);
    opacity: 0;
  }
}
@keyframes u1qz6lk {
  100%, 0% {
    background-color: var(--slab-phase1-color);
  }
  24.9% {
    background-color: var(--slab-phase1-color);
  }
  25% {
    background-color: var(--slab-phase2-color, var(--slab-phase1-color));
  }
  49.9% {
    background-color: var(--slab-phase2-color, var(--slab-phase1-color));
  }
  50% {
    background-color: var(--slab-phase3-color, var(--slab-phase1-color));
  }
  74.9% {
    background-color: var(--slab-phase3-color, var(--slab-phase1-color));
  }
  75% {
    background-color: var(--slab-phase4-color, var(--slab-phase1-color));
  }
  99.9% {
    background-color: var(--slab-phase4-color, var(--slab-phase1-color));
  }
}`);Array.from({length:4},function(a,r){return"--slab-phase".concat(r+1,"-color")});ut(`.lifeline-rli-bounding-box {
  --life-line-phase1-color: rgb(50, 205, 50);
  font-size: 16px;
  isolation: isolate;
  color: var(--life-line-phase1-color);
}
.lifeline-rli-bounding-box .lifeline-indicator {
  position: relative;
  text-align: center;
}
.lifeline-rli-bounding-box .lifeline-indicator path.rli-lifeline {
  stroke-dasharray: 474.7616760254 30.3039367676;
  animation: var(--rli-animation-duration, 2s) var(--rli-animation-function, linear) infinite u1qz6lr, calc(var(--rli-animation-duration, 2s) * 4) var(--rli-animation-function, linear) infinite u1qz6m8;
}
.lifeline-rli-bounding-box .lifeline-text {
  color: currentColor;
  mix-blend-mode: difference;
  width: unset;
  display: block;
}

@property --life-line-phase1-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --life-line-phase2-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --life-line-phase3-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --life-line-phase4-color {
  syntax: "<color>";
  inherits: true;
  initial-value: rgb(50, 205, 50);
}
@property --rli-animation-duration {
  syntax: "<time>";
  inherits: true;
  initial-value: 2s;
}
@keyframes u1qz6lr {
  to {
    stroke-dashoffset: -1010.1312255859;
  }
}
@keyframes u1qz6m8 {
  100%, 0% {
    color: var(--life-line-phase1-color);
  }
  20% {
    color: var(--life-line-phase1-color);
  }
  25% {
    color: var(--life-line-phase2-color, var(--life-line-phase1-color));
  }
  45% {
    color: var(--life-line-phase2-color, var(--life-line-phase1-color));
  }
  50% {
    color: var(--life-line-phase3-color, var(--life-line-phase1-color));
  }
  70% {
    color: var(--life-line-phase3-color, var(--life-line-phase1-color));
  }
  75% {
    color: var(--life-line-phase4-color, var(--life-line-phase1-color));
  }
  95% {
    color: var(--life-line-phase4-color, var(--life-line-phase1-color));
  }
}`);Array.from({length:4},function(a,r){return"--life-line-phase".concat(r+1,"-color")});var kp={color:void 0,size:void 0,className:void 0,style:void 0,attr:void 0},Em=X.createContext&&X.createContext(kp),m5=["attr","size","title"];function p5(a,r){if(a==null)return{};var i=v5(a,r),u,s;if(Object.getOwnPropertySymbols){var f=Object.getOwnPropertySymbols(a);for(s=0;s<f.length;s++)u=f[s],!(r.indexOf(u)>=0)&&Object.prototype.propertyIsEnumerable.call(a,u)&&(i[u]=a[u])}return i}function v5(a,r){if(a==null)return{};var i={};for(var u in a)if(Object.prototype.hasOwnProperty.call(a,u)){if(r.indexOf(u)>=0)continue;i[u]=a[u]}return i}function Xo(){return Xo=Object.assign?Object.assign.bind():function(a){for(var r=1;r<arguments.length;r++){var i=arguments[r];for(var u in i)Object.prototype.hasOwnProperty.call(i,u)&&(a[u]=i[u])}return a},Xo.apply(this,arguments)}function Om(a,r){var i=Object.keys(a);if(Object.getOwnPropertySymbols){var u=Object.getOwnPropertySymbols(a);r&&(u=u.filter(function(s){return Object.getOwnPropertyDescriptor(a,s).enumerable})),i.push.apply(i,u)}return i}function Go(a){for(var r=1;r<arguments.length;r++){var i=arguments[r]!=null?arguments[r]:{};r%2?Om(Object(i),!0).forEach(function(u){b5(a,u,i[u])}):Object.getOwnPropertyDescriptors?Object.defineProperties(a,Object.getOwnPropertyDescriptors(i)):Om(Object(i)).forEach(function(u){Object.defineProperty(a,u,Object.getOwnPropertyDescriptor(i,u))})}return a}function b5(a,r,i){return r=g5(r),r in a?Object.defineProperty(a,r,{value:i,enumerable:!0,configurable:!0,writable:!0}):a[r]=i,a}function g5(a){var r=y5(a,"string");return typeof r=="symbol"?r:r+""}function y5(a,r){if(typeof a!="object"||!a)return a;var i=a[Symbol.toPrimitive];if(i!==void 0){var u=i.call(a,r);if(typeof u!="object")return u;throw new TypeError("@@toPrimitive must return a primitive value.")}return(r==="string"?String:Number)(a)}function Np(a){return a&&a.map((r,i)=>X.createElement(r.tag,Go({key:i},r.attr),Np(r.child)))}function Aa(a){return r=>X.createElement(x5,Xo({attr:Go({},a.attr)},r),Np(a.child))}function x5(a){var r=i=>{var{attr:u,size:s,title:f}=a,m=p5(a,m5),v=s||i.size||"1em",h;return i.className&&(h=i.className),a.className&&(h=(h?h+" ":"")+a.className),X.createElement("svg",Xo({stroke:"currentColor",fill:"currentColor",strokeWidth:"0"},i.attr,u,m,{className:h,style:Go(Go({color:a.color||i.color},i.style),a.style),height:v,width:v,xmlns:"http://www.w3.org/2000/svg"}),f&&X.createElement("title",null,f),a.children)};return Em!==void 0?X.createElement(Em.Consumer,null,i=>r(i)):r(kp)}function lx(a){return Aa({attr:{viewBox:"0 0 448 512"},child:[{tag:"path",attr:{d:"M257.5 445.1l-22.2 22.2c-9.4 9.4-24.6 9.4-33.9 0L7 273c-9.4-9.4-9.4-24.6 0-33.9L201.4 44.7c9.4-9.4 24.6-9.4 33.9 0l22.2 22.2c9.5 9.5 9.3 25-.4 34.3L136.6 216H424c13.3 0 24 10.7 24 24v32c0 13.3-10.7 24-24 24H136.6l120.5 114.8c9.8 9.3 10 24.8.4 34.3z"},child:[]}]})(a)}function rx(a){return Aa({attr:{viewBox:"0 0 448 512"},child:[{tag:"path",attr:{d:"M190.5 66.9l22.2-22.2c9.4-9.4 24.6-9.4 33.9 0L441 239c9.4 9.4 9.4 24.6 0 33.9L246.6 467.3c-9.4 9.4-24.6 9.4-33.9 0l-22.2-22.2c-9.5-9.5-9.3-25 .4-34.3L311.4 296H24c-13.3 0-24-10.7-24-24v-32c0-13.3 10.7-24 24-24h287.4L190.9 101.2c-9.8-9.3-10-24.8-.4-34.3z"},child:[]}]})(a)}function ix(a){return Aa({attr:{viewBox:"0 0 576 512"},child:[{tag:"path",attr:{d:"M402.6 83.2l90.2 90.2c3.8 3.8 3.8 10 0 13.8L274.4 405.6l-92.8 10.3c-12.4 1.4-22.9-9.1-21.5-21.5l10.3-92.8L388.8 83.2c3.8-3.8 10-3.8 13.8 0zm162-22.9l-48.8-48.8c-15.2-15.2-39.9-15.2-55.2 0l-35.4 35.4c-3.8 3.8-3.8 10 0 13.8l90.2 90.2c3.8 3.8 10 3.8 13.8 0l35.4-35.4c15.2-15.3 15.2-40 0-55.2zM384 346.2V448H64V128h229.8c3.2 0 6.2-1.3 8.5-3.5l40-40c7.6-7.6 2.2-20.5-8.5-20.5H48C21.5 64 0 85.5 0 112v352c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48V306.2c0-10.7-12.9-16-20.5-8.5l-40 40c-2.2 2.3-3.5 5.3-3.5 8.5z"},child:[]}]})(a)}function ox(a){return Aa({attr:{viewBox:"0 0 576 512"},child:[{tag:"path",attr:{d:"M384 121.9c0-6.3-2.5-12.4-7-16.9L279.1 7c-4.5-4.5-10.6-7-17-7H256v128h128zM571 308l-95.7-96.4c-10.1-10.1-27.4-3-27.4 11.3V288h-64v64h64v65.2c0 14.3 17.3 21.4 27.4 11.3L571 332c6.6-6.6 6.6-17.4 0-24zm-379 28v-32c0-8.8 7.2-16 16-16h176V160H248c-13.2 0-24-10.8-24-24V0H24C10.7 0 0 10.7 0 24v464c0 13.3 10.7 24 24 24h336c13.3 0 24-10.7 24-24V352H208c-8.8 0-16-7.2-16-16z"},child:[]}]})(a)}function ux(a){return Aa({attr:{viewBox:"0 0 448 512"},child:[{tag:"path",attr:{d:"M416 208H272V64c0-17.67-14.33-32-32-32h-32c-17.67 0-32 14.33-32 32v144H32c-17.67 0-32 14.33-32 32v32c0 17.67 14.33 32 32 32h144v144c0 17.67 14.33 32 32 32h32c17.67 0 32-14.33 32-32V304h144c17.67 0 32-14.33 32-32v-32c0-17.67-14.33-32-32-32z"},child:[]}]})(a)}function cx(a){return Aa({attr:{viewBox:"0 0 448 512"},child:[{tag:"path",attr:{d:"M433.941 129.941l-83.882-83.882A48 48 0 0 0 316.118 32H48C21.49 32 0 53.49 0 80v352c0 26.51 21.49 48 48 48h352c26.51 0 48-21.49 48-48V163.882a48 48 0 0 0-14.059-33.941zM224 416c-35.346 0-64-28.654-64-64 0-35.346 28.654-64 64-64s64 28.654 64 64c0 35.346-28.654 64-64 64zm96-304.52V212c0 6.627-5.373 12-12 12H76c-6.627 0-12-5.373-12-12V108c0-6.627 5.373-12 12-12h228.52c3.183 0 6.235 1.264 8.485 3.515l3.48 3.48A11.996 11.996 0 0 1 320 111.48z"},child:[]}]})(a)}function sx(a){return Aa({attr:{viewBox:"0 0 448 512"},child:[{tag:"path",attr:{d:"M432 32H312l-9.4-18.7A24 24 0 0 0 281.1 0H166.8a23.72 23.72 0 0 0-21.4 13.3L136 32H16A16 16 0 0 0 0 48v32a16 16 0 0 0 16 16h416a16 16 0 0 0 16-16V48a16 16 0 0 0-16-16zM53.2 467a48 48 0 0 0 47.9 45h245.8a48 48 0 0 0 47.9-45L416 128H32z"},child:[]}]})(a)}function fx(a){return Aa({attr:{viewBox:"0 0 512 512"},child:[{tag:"path",attr:{d:"M212.333 224.333H12c-6.627 0-12-5.373-12-12V12C0 5.373 5.373 0 12 0h48c6.627 0 12 5.373 12 12v78.112C117.773 39.279 184.26 7.47 258.175 8.007c136.906.994 246.448 111.623 246.157 248.532C504.041 393.258 393.12 504 256.333 504c-64.089 0-122.496-24.313-166.51-64.215-5.099-4.622-5.334-12.554-.467-17.42l33.967-33.967c4.474-4.474 11.662-4.717 16.401-.525C170.76 415.336 211.58 432 256.333 432c97.268 0 176-78.716 176-176 0-97.267-78.716-176-176-176-58.496 0-110.28 28.476-142.274 72.333h98.274c6.627 0 12 5.373 12 12v48c0 6.627-5.373 12-12 12z"},child:[]}]})(a)}var fi=a=>a.type==="checkbox",Ja=a=>a instanceof Date,_t=a=>a==null;const Up=a=>typeof a=="object";var et=a=>!_t(a)&&!Array.isArray(a)&&Up(a)&&!Ja(a),qp=a=>et(a)&&a.target?fi(a.target)?a.target.checked:a.target.value:a,S5=a=>a.substring(0,a.search(/\.\d+(\.|$)/))||a,Hp=(a,r)=>a.has(S5(r)),E5=a=>{const r=a.constructor&&a.constructor.prototype;return et(r)&&r.hasOwnProperty("isPrototypeOf")},lf=typeof window<"u"&&typeof window.HTMLElement<"u"&&typeof document<"u";function gt(a){let r;const i=Array.isArray(a),u=typeof FileList<"u"?a instanceof FileList:!1;if(a instanceof Date)r=new Date(a);else if(a instanceof Set)r=new Set(a);else if(!(lf&&(a instanceof Blob||u))&&(i||et(a)))if(r=i?[]:{},!i&&!E5(a))r=a;else for(const s in a)a.hasOwnProperty(s)&&(r[s]=gt(a[s]));else return a;return r}var iu=a=>Array.isArray(a)?a.filter(Boolean):[],Ie=a=>a===void 0,K=(a,r,i)=>{if(!r||!et(a))return i;const u=iu(r.split(/[,[\].]+?/)).reduce((s,f)=>_t(s)?s:s[f],a);return Ie(u)||u===a?Ie(a[r])?i:a[r]:u},Bt=a=>typeof a=="boolean",rf=a=>/^\w*$/.test(a),Lp=a=>iu(a.replace(/["|']|\]/g,"").split(/\.|\[/)),qe=(a,r,i)=>{let u=-1;const s=rf(r)?[r]:Lp(r),f=s.length,m=f-1;for(;++u<f;){const v=s[u];let h=i;if(u!==m){const p=a[v];h=et(p)||Array.isArray(p)?p:isNaN(+s[u+1])?{}:[]}if(v==="__proto__"||v==="constructor"||v==="prototype")return;a[v]=h,a=a[v]}};const Qo={BLUR:"blur",FOCUS_OUT:"focusout",CHANGE:"change"},un={onBlur:"onBlur",onChange:"onChange",onSubmit:"onSubmit",onTouched:"onTouched",all:"all"},Yn={max:"max",min:"min",maxLength:"maxLength",minLength:"minLength",pattern:"pattern",required:"required",validate:"validate"},O5=X.createContext(null),of=()=>X.useContext(O5);var Bp=(a,r,i,u=!0)=>{const s={defaultValues:r._defaultValues};for(const f in a)Object.defineProperty(s,f,{get:()=>{const m=f;return r._proxyFormState[m]!==un.all&&(r._proxyFormState[m]=!u||un.all),i&&(i[m]=!0),a[m]}});return s};const uf=typeof window<"u"?y.useLayoutEffect:y.useEffect;function A5(a){const r=of(),{control:i=r.control,disabled:u,name:s,exact:f}=a||{},[m,v]=X.useState(i._formState),h=X.useRef({isDirty:!1,isLoading:!1,dirtyFields:!1,touchedFields:!1,validatingFields:!1,isValidating:!1,isValid:!1,errors:!1});return uf(()=>i._subscribe({name:s,formState:h.current,exact:f,callback:p=>{!u&&v({...i._formState,...p})}}),[s,u,f]),X.useEffect(()=>{h.current.isValid&&i._setValid(!0)},[i]),X.useMemo(()=>Bp(m,i,h.current,!1),[m,i])}var xn=a=>typeof a=="string",Vp=(a,r,i,u,s)=>xn(a)?(u&&r.watch.add(a),K(i,a,s)):Array.isArray(a)?a.map(f=>(u&&r.watch.add(f),K(i,f))):(u&&(r.watchAll=!0),i);function T5(a){const r=of(),{control:i=r.control,name:u,defaultValue:s,disabled:f,exact:m}=a||{},v=X.useRef(s),[h,p]=X.useState(i._getWatch(u,v.current));return uf(()=>i._subscribe({name:u,formState:{values:!0},exact:m,callback:g=>!f&&p(Vp(u,i._names,g.values||i._formValues,!1,v.current))}),[u,i,f,m]),X.useEffect(()=>i._removeUnmounted()),h}function _5(a){const r=of(),{name:i,disabled:u,control:s=r.control,shouldUnregister:f}=a,m=Hp(s._names.array,i),v=T5({control:s,name:i,defaultValue:K(s._formValues,i,K(s._defaultValues,i,a.defaultValue)),exact:!0}),h=A5({control:s,name:i,exact:!0}),p=X.useRef(a),g=X.useRef(s.register(i,{...a.rules,value:v,...Bt(a.disabled)?{disabled:a.disabled}:{}})),z=X.useMemo(()=>Object.defineProperties({},{invalid:{enumerable:!0,get:()=>!!K(h.errors,i)},isDirty:{enumerable:!0,get:()=>!!K(h.dirtyFields,i)},isTouched:{enumerable:!0,get:()=>!!K(h.touchedFields,i)},isValidating:{enumerable:!0,get:()=>!!K(h.validatingFields,i)},error:{enumerable:!0,get:()=>K(h.errors,i)}}),[h,i]),M=X.useCallback(R=>g.current.onChange({target:{value:qp(R),name:i},type:Qo.CHANGE}),[i]),w=X.useCallback(()=>g.current.onBlur({target:{value:K(s._formValues,i),name:i},type:Qo.BLUR}),[i,s._formValues]),A=X.useCallback(R=>{const q=K(s._fields,i);q&&R&&(q._f.ref={focus:()=>R.focus(),select:()=>R.select(),setCustomValidity:N=>R.setCustomValidity(N),reportValidity:()=>R.reportValidity()})},[s._fields,i]),E=X.useMemo(()=>({name:i,value:v,...Bt(u)||h.disabled?{disabled:h.disabled||u}:{},onChange:M,onBlur:w,ref:A}),[i,u,h.disabled,M,w,A,v]);return X.useEffect(()=>{const R=s._options.shouldUnregister||f;s.register(i,{...p.current.rules,...Bt(p.current.disabled)?{disabled:p.current.disabled}:{}});const q=(N,V)=>{const F=K(s._fields,N);F&&F._f&&(F._f.mount=V)};if(q(i,!0),R){const N=gt(K(s._options.defaultValues,i));qe(s._defaultValues,i,N),Ie(K(s._formValues,i))&&qe(s._formValues,i,N)}return!m&&s.register(i),()=>{(m?R&&!s._state.action:R)?s.unregister(i):q(i,!1)}},[i,s,m,f]),X.useEffect(()=>{s._setDisabledField({disabled:u,name:i})},[u,i,s]),X.useMemo(()=>({field:E,formState:h,fieldState:z}),[E,h,z])}const dx=a=>a.render(_5(a));var z5=(a,r,i,u,s)=>r?{...i[a],types:{...i[a]&&i[a].types?i[a].types:{},[u]:s||!0}}:{},ei=a=>Array.isArray(a)?a:[a],Am=()=>{let a=[];return{get observers(){return a},next:s=>{for(const f of a)f.next&&f.next(s)},subscribe:s=>(a.push(s),{unsubscribe:()=>{a=a.filter(f=>f!==s)}}),unsubscribe:()=>{a=[]}}},Vs=a=>_t(a)||!Up(a);function xa(a,r){if(Vs(a)||Vs(r))return a===r;if(Ja(a)&&Ja(r))return a.getTime()===r.getTime();const i=Object.keys(a),u=Object.keys(r);if(i.length!==u.length)return!1;for(const s of i){const f=a[s];if(!u.includes(s))return!1;if(s!=="ref"){const m=r[s];if(Ja(f)&&Ja(m)||et(f)&&et(m)||Array.isArray(f)&&Array.isArray(m)?!xa(f,m):f!==m)return!1}}return!0}var Tt=a=>et(a)&&!Object.keys(a).length,cf=a=>a.type==="file",cn=a=>typeof a=="function",Zo=a=>{if(!lf)return!1;const r=a?a.ownerDocument:0;return a instanceof(r&&r.defaultView?r.defaultView.HTMLElement:HTMLElement)},jp=a=>a.type==="select-multiple",sf=a=>a.type==="radio",D5=a=>sf(a)||fi(a),As=a=>Zo(a)&&a.isConnected;function w5(a,r){const i=r.slice(0,-1).length;let u=0;for(;u<i;)a=Ie(a)?u++:a[r[u++]];return a}function R5(a){for(const r in a)if(a.hasOwnProperty(r)&&!Ie(a[r]))return!1;return!0}function ot(a,r){const i=Array.isArray(r)?r:rf(r)?[r]:Lp(r),u=i.length===1?a:w5(a,i),s=i.length-1,f=i[s];return u&&delete u[f],s!==0&&(et(u)&&Tt(u)||Array.isArray(u)&&R5(u))&&ot(a,i.slice(0,-1)),a}var Yp=a=>{for(const r in a)if(cn(a[r]))return!0;return!1};function $o(a,r={}){const i=Array.isArray(a);if(et(a)||i)for(const u in a)Array.isArray(a[u])||et(a[u])&&!Yp(a[u])?(r[u]=Array.isArray(a[u])?[]:{},$o(a[u],r[u])):_t(a[u])||(r[u]=!0);return r}function Xp(a,r,i){const u=Array.isArray(a);if(et(a)||u)for(const s in a)Array.isArray(a[s])||et(a[s])&&!Yp(a[s])?Ie(r)||Vs(i[s])?i[s]=Array.isArray(a[s])?$o(a[s],[]):{...$o(a[s])}:Xp(a[s],_t(r)?{}:r[s],i[s]):i[s]=!xa(a[s],r[s]);return i}var Pr=(a,r)=>Xp(a,r,$o(r));const Tm={value:!1,isValid:!1},_m={value:!0,isValid:!0};var Gp=a=>{if(Array.isArray(a)){if(a.length>1){const r=a.filter(i=>i&&i.checked&&!i.disabled).map(i=>i.value);return{value:r,isValid:!!r.length}}return a[0].checked&&!a[0].disabled?a[0].attributes&&!Ie(a[0].attributes.value)?Ie(a[0].value)||a[0].value===""?_m:{value:a[0].value,isValid:!0}:_m:Tm}return Tm},Qp=(a,{valueAsNumber:r,valueAsDate:i,setValueAs:u})=>Ie(a)?a:r?a===""?NaN:a&&+a:i&&xn(a)?new Date(a):u?u(a):a;const zm={isValid:!1,value:null};var Zp=a=>Array.isArray(a)?a.reduce((r,i)=>i&&i.checked&&!i.disabled?{isValid:!0,value:i.value}:r,zm):zm;function Dm(a){const r=a.ref;return cf(r)?r.files:sf(r)?Zp(a.refs).value:jp(r)?[...r.selectedOptions].map(({value:i})=>i):fi(r)?Gp(a.refs).value:Qp(Ie(r.value)?a.ref.value:r.value,a)}var M5=(a,r,i,u)=>{const s={};for(const f of a){const m=K(r,f);m&&qe(s,f,m._f)}return{criteriaMode:i,names:[...a],fields:s,shouldUseNativeValidation:u}},Po=a=>a instanceof RegExp,Fr=a=>Ie(a)?a:Po(a)?a.source:et(a)?Po(a.value)?a.value.source:a.value:a,wm=a=>({isOnSubmit:!a||a===un.onSubmit,isOnBlur:a===un.onBlur,isOnChange:a===un.onChange,isOnAll:a===un.all,isOnTouch:a===un.onTouched});const Rm="AsyncFunction";var C5=a=>!!a&&!!a.validate&&!!(cn(a.validate)&&a.validate.constructor.name===Rm||et(a.validate)&&Object.values(a.validate).find(r=>r.constructor.name===Rm)),k5=a=>a.mount&&(a.required||a.min||a.max||a.maxLength||a.minLength||a.pattern||a.validate),Mm=(a,r,i)=>!i&&(r.watchAll||r.watch.has(a)||[...r.watch].some(u=>a.startsWith(u)&&/^\.\w+/.test(a.slice(u.length))));const ti=(a,r,i,u)=>{for(const s of i||Object.keys(a)){const f=K(a,s);if(f){const{_f:m,...v}=f;if(m){if(m.refs&&m.refs[0]&&r(m.refs[0],s)&&!u)return!0;if(m.ref&&r(m.ref,m.name)&&!u)return!0;if(ti(v,r))break}else if(et(v)&&ti(v,r))break}}};function Cm(a,r,i){const u=K(a,i);if(u||rf(i))return{error:u,name:i};const s=i.split(".");for(;s.length;){const f=s.join("."),m=K(r,f),v=K(a,f);if(m&&!Array.isArray(m)&&i!==f)return{name:i};if(v&&v.type)return{name:f,error:v};s.pop()}return{name:i}}var N5=(a,r,i,u)=>{i(a);const{name:s,...f}=a;return Tt(f)||Object.keys(f).length>=Object.keys(r).length||Object.keys(f).find(m=>r[m]===(!u||un.all))},U5=(a,r,i)=>!a||!r||a===r||ei(a).some(u=>u&&(i?u===r:u.startsWith(r)||r.startsWith(u))),q5=(a,r,i,u,s)=>s.isOnAll?!1:!i&&s.isOnTouch?!(r||a):(i?u.isOnBlur:s.isOnBlur)?!a:(i?u.isOnChange:s.isOnChange)?a:!0,H5=(a,r)=>!iu(K(a,r)).length&&ot(a,r),L5=(a,r,i)=>{const u=ei(K(a,i));return qe(u,"root",r[i]),qe(a,i,u),a},qo=a=>xn(a);function km(a,r,i="validate"){if(qo(a)||Array.isArray(a)&&a.every(qo)||Bt(a)&&!a)return{type:i,message:qo(a)?a:"",ref:r}}var Vl=a=>et(a)&&!Po(a)?a:{value:a,message:""},Nm=async(a,r,i,u,s,f)=>{const{ref:m,refs:v,required:h,maxLength:p,minLength:g,min:z,max:M,pattern:w,validate:A,name:E,valueAsNumber:R,mount:q}=a._f,N=K(i,E);if(!q||r.has(E))return{};const V=v?v[0]:m,F=oe=>{s&&V.reportValidity&&(V.setCustomValidity(Bt(oe)?"":oe||""),V.reportValidity())},$={},me=sf(m),pe=fi(m),ve=me||pe,G=(R||cf(m))&&Ie(m.value)&&Ie(N)||Zo(m)&&m.value===""||N===""||Array.isArray(N)&&!N.length,W=z5.bind(null,E,u,$),ce=(oe,re,ge,Te=Yn.maxLength,Be=Yn.minLength)=>{const tt=oe?re:ge;$[E]={type:oe?Te:Be,message:tt,ref:m,...W(oe?Te:Be,tt)}};if(f?!Array.isArray(N)||!N.length:h&&(!ve&&(G||_t(N))||Bt(N)&&!N||pe&&!Gp(v).isValid||me&&!Zp(v).isValid)){const{value:oe,message:re}=qo(h)?{value:!!h,message:h}:Vl(h);if(oe&&($[E]={type:Yn.required,message:re,ref:V,...W(Yn.required,re)},!u))return F(re),$}if(!G&&(!_t(z)||!_t(M))){let oe,re;const ge=Vl(M),Te=Vl(z);if(!_t(N)&&!isNaN(N)){const Be=m.valueAsNumber||N&&+N;_t(ge.value)||(oe=Be>ge.value),_t(Te.value)||(re=Be<Te.value)}else{const Be=m.valueAsDate||new Date(N),tt=T=>new Date(new Date().toDateString()+" "+T),Ve=m.type=="time",Je=m.type=="week";xn(ge.value)&&N&&(oe=Ve?tt(N)>tt(ge.value):Je?N>ge.value:Be>new Date(ge.value)),xn(Te.value)&&N&&(re=Ve?tt(N)<tt(Te.value):Je?N<Te.value:Be<new Date(Te.value))}if((oe||re)&&(ce(!!oe,ge.message,Te.message,Yn.max,Yn.min),!u))return F($[E].message),$}if((p||g)&&!G&&(xn(N)||f&&Array.isArray(N))){const oe=Vl(p),re=Vl(g),ge=!_t(oe.value)&&N.length>+oe.value,Te=!_t(re.value)&&N.length<+re.value;if((ge||Te)&&(ce(ge,oe.message,re.message),!u))return F($[E].message),$}if(w&&!G&&xn(N)){const{value:oe,message:re}=Vl(w);if(Po(oe)&&!N.match(oe)&&($[E]={type:Yn.pattern,message:re,ref:m,...W(Yn.pattern,re)},!u))return F(re),$}if(A){if(cn(A)){const oe=await A(N,i),re=km(oe,V);if(re&&($[E]={...re,...W(Yn.validate,re.message)},!u))return F(re.message),$}else if(et(A)){let oe={};for(const re in A){if(!Tt(oe)&&!u)break;const ge=km(await A[re](N,i),V,re);ge&&(oe={...ge,...W(re,ge.message)},F(ge.message),u&&($[E]=oe))}if(!Tt(oe)&&($[E]={ref:V,...oe},!u))return $}}return F(!0),$};const B5={mode:un.onSubmit,reValidateMode:un.onChange,shouldFocusError:!0};function V5(a={}){let r={...B5,...a},i={submitCount:0,isDirty:!1,isReady:!1,isLoading:cn(r.defaultValues),isValidating:!1,isSubmitted:!1,isSubmitting:!1,isSubmitSuccessful:!1,isValid:!1,touchedFields:{},dirtyFields:{},validatingFields:{},errors:r.errors||{},disabled:r.disabled||!1};const u={};let s=et(r.defaultValues)||et(r.values)?gt(r.defaultValues||r.values)||{}:{},f=r.shouldUnregister?{}:gt(s),m={action:!1,mount:!1,watch:!1},v={mount:new Set,disabled:new Set,unMount:new Set,array:new Set,watch:new Set},h,p=0;const g={isDirty:!1,dirtyFields:!1,validatingFields:!1,touchedFields:!1,isValidating:!1,isValid:!1,errors:!1};let z={...g};const M={array:Am(),state:Am()},w=wm(r.mode),A=wm(r.reValidateMode),E=r.criteriaMode===un.all,R=S=>U=>{clearTimeout(p),p=setTimeout(S,U)},q=async S=>{if(!r.disabled&&(g.isValid||z.isValid||S)){const U=r.resolver?Tt((await G()).errors):await ce(u,!0);U!==i.isValid&&M.state.next({isValid:U})}},N=(S,U)=>{!r.disabled&&(g.isValidating||g.validatingFields||z.isValidating||z.validatingFields)&&((S||Array.from(v.mount)).forEach(H=>{H&&(U?qe(i.validatingFields,H,U):ot(i.validatingFields,H))}),M.state.next({validatingFields:i.validatingFields,isValidating:!Tt(i.validatingFields)}))},V=(S,U=[],H,J,P=!0,Z=!0)=>{if(J&&H&&!r.disabled){if(m.action=!0,Z&&Array.isArray(K(u,S))){const I=H(K(u,S),J.argA,J.argB);P&&qe(u,S,I)}if(Z&&Array.isArray(K(i.errors,S))){const I=H(K(i.errors,S),J.argA,J.argB);P&&qe(i.errors,S,I),H5(i.errors,S)}if((g.touchedFields||z.touchedFields)&&Z&&Array.isArray(K(i.touchedFields,S))){const I=H(K(i.touchedFields,S),J.argA,J.argB);P&&qe(i.touchedFields,S,I)}(g.dirtyFields||z.dirtyFields)&&(i.dirtyFields=Pr(s,f)),M.state.next({name:S,isDirty:re(S,U),dirtyFields:i.dirtyFields,errors:i.errors,isValid:i.isValid})}else qe(f,S,U)},F=(S,U)=>{qe(i.errors,S,U),M.state.next({errors:i.errors})},$=S=>{i.errors=S,M.state.next({errors:i.errors,isValid:!1})},me=(S,U,H,J)=>{const P=K(u,S);if(P){const Z=K(f,S,Ie(H)?K(s,S):H);Ie(Z)||J&&J.defaultChecked||U?qe(f,S,U?Z:Dm(P._f)):Be(S,Z),m.mount&&q()}},pe=(S,U,H,J,P)=>{let Z=!1,I=!1;const ze={name:S};if(!r.disabled){if(!H||J){(g.isDirty||z.isDirty)&&(I=i.isDirty,i.isDirty=ze.isDirty=re(),Z=I!==ze.isDirty);const Xe=xa(K(s,S),U);I=!!K(i.dirtyFields,S),Xe?ot(i.dirtyFields,S):qe(i.dirtyFields,S,!0),ze.dirtyFields=i.dirtyFields,Z=Z||(g.dirtyFields||z.dirtyFields)&&I!==!Xe}if(H){const Xe=K(i.touchedFields,S);Xe||(qe(i.touchedFields,S,H),ze.touchedFields=i.touchedFields,Z=Z||(g.touchedFields||z.touchedFields)&&Xe!==H)}Z&&P&&M.state.next(ze)}return Z?ze:{}},ve=(S,U,H,J)=>{const P=K(i.errors,S),Z=(g.isValid||z.isValid)&&Bt(U)&&i.isValid!==U;if(r.delayError&&H?(h=R(()=>F(S,H)),h(r.delayError)):(clearTimeout(p),h=null,H?qe(i.errors,S,H):ot(i.errors,S)),(H?!xa(P,H):P)||!Tt(J)||Z){const I={...J,...Z&&Bt(U)?{isValid:U}:{},errors:i.errors,name:S};i={...i,...I},M.state.next(I)}},G=async S=>{N(S,!0);const U=await r.resolver(f,r.context,M5(S||v.mount,u,r.criteriaMode,r.shouldUseNativeValidation));return N(S),U},W=async S=>{const{errors:U}=await G(S);if(S)for(const H of S){const J=K(U,H);J?qe(i.errors,H,J):ot(i.errors,H)}else i.errors=U;return U},ce=async(S,U,H={valid:!0})=>{for(const J in S){const P=S[J];if(P){const{_f:Z,...I}=P;if(Z){const ze=v.array.has(Z.name),Xe=P._f&&C5(P._f);Xe&&g.validatingFields&&N([J],!0);const ct=await Nm(P,v.disabled,f,E,r.shouldUseNativeValidation&&!U,ze);if(Xe&&g.validatingFields&&N([J]),ct[Z.name]&&(H.valid=!1,U))break;!U&&(K(ct,Z.name)?ze?L5(i.errors,ct,Z.name):qe(i.errors,Z.name,ct[Z.name]):ot(i.errors,Z.name))}!Tt(I)&&await ce(I,U,H)}}return H.valid},oe=()=>{for(const S of v.unMount){const U=K(u,S);U&&(U._f.refs?U._f.refs.every(H=>!As(H)):!As(U._f.ref))&&Me(S)}v.unMount=new Set},re=(S,U)=>!r.disabled&&(S&&U&&qe(f,S,U),!xa(ue(),s)),ge=(S,U,H)=>Vp(S,v,{...m.mount?f:Ie(U)?s:xn(S)?{[S]:U}:U},H,U),Te=S=>iu(K(m.mount?f:s,S,r.shouldUnregister?K(s,S,[]):[])),Be=(S,U,H={})=>{const J=K(u,S);let P=U;if(J){const Z=J._f;Z&&(!Z.disabled&&qe(f,S,Qp(U,Z)),P=Zo(Z.ref)&&_t(U)?"":U,jp(Z.ref)?[...Z.ref.options].forEach(I=>I.selected=P.includes(I.value)):Z.refs?fi(Z.ref)?Z.refs.length>1?Z.refs.forEach(I=>(!I.defaultChecked||!I.disabled)&&(I.checked=Array.isArray(P)?!!P.find(ze=>ze===I.value):P===I.value)):Z.refs[0]&&(Z.refs[0].checked=!!P):Z.refs.forEach(I=>I.checked=I.value===P):cf(Z.ref)?Z.ref.value="":(Z.ref.value=P,Z.ref.type||M.state.next({name:S,values:gt(f)})))}(H.shouldDirty||H.shouldTouch)&&pe(S,P,H.shouldTouch,H.shouldDirty,!0),H.shouldValidate&&Y(S)},tt=(S,U,H)=>{for(const J in U){const P=U[J],Z=`${S}.${J}`,I=K(u,Z);(v.array.has(S)||et(P)||I&&!I._f)&&!Ja(P)?tt(Z,P,H):Be(Z,P,H)}},Ve=(S,U,H={})=>{const J=K(u,S),P=v.array.has(S),Z=gt(U);qe(f,S,Z),P?(M.array.next({name:S,values:gt(f)}),(g.isDirty||g.dirtyFields||z.isDirty||z.dirtyFields)&&H.shouldDirty&&M.state.next({name:S,dirtyFields:Pr(s,f),isDirty:re(S,Z)})):J&&!J._f&&!_t(Z)?tt(S,Z,H):Be(S,Z,H),Mm(S,v)&&M.state.next({...i}),M.state.next({name:m.mount?S:void 0,values:gt(f)})},Je=async S=>{m.mount=!0;const U=S.target;let H=U.name,J=!0;const P=K(u,H),Z=I=>{J=Number.isNaN(I)||Ja(I)&&isNaN(I.getTime())||xa(I,K(f,H,I))};if(P){let I,ze;const Xe=U.type?Dm(P._f):qp(S),ct=S.type===Qo.BLUR||S.type===Qo.FOCUS_OUT,ou=!k5(P._f)&&!r.resolver&&!K(i.errors,H)&&!P._f.deps||q5(ct,K(i.touchedFields,H),i.isSubmitted,A,w),On=Mm(H,v,ct);qe(f,H,Xe),ct?(P._f.onBlur&&P._f.onBlur(S),h&&h(0)):P._f.onChange&&P._f.onChange(S);const mt=pe(H,Xe,ct),uu=!Tt(mt)||On;if(!ct&&M.state.next({name:H,type:S.type,values:gt(f)}),ou)return(g.isValid||z.isValid)&&(r.mode==="onBlur"?ct&&q():ct||q()),uu&&M.state.next({name:H,...On?{}:mt});if(!ct&&On&&M.state.next({...i}),r.resolver){const{errors:tn}=await G([H]);if(Z(Xe),J){const Et=Cm(i.errors,u,H),hi=Cm(tn,u,Et.name||H);I=hi.error,H=hi.name,ze=Tt(tn)}}else N([H],!0),I=(await Nm(P,v.disabled,f,E,r.shouldUseNativeValidation))[H],N([H]),Z(Xe),J&&(I?ze=!1:(g.isValid||z.isValid)&&(ze=await ce(u,!0)));J&&(P._f.deps&&Y(P._f.deps),ve(H,ze,I,mt))}},T=(S,U)=>{if(K(i.errors,U)&&S.focus)return S.focus(),1},Y=async(S,U={})=>{let H,J;const P=ei(S);if(r.resolver){const Z=await W(Ie(S)?S:P);H=Tt(Z),J=S?!P.some(I=>K(Z,I)):H}else S?(J=(await Promise.all(P.map(async Z=>{const I=K(u,Z);return await ce(I&&I._f?{[Z]:I}:I)}))).every(Boolean),!(!J&&!i.isValid)&&q()):J=H=await ce(u);return M.state.next({...!xn(S)||(g.isValid||z.isValid)&&H!==i.isValid?{}:{name:S},...r.resolver||!S?{isValid:H}:{},errors:i.errors}),U.shouldFocus&&!J&&ti(u,T,S?P:v.mount),J},ue=S=>{const U={...m.mount?f:s};return Ie(S)?U:xn(S)?K(U,S):S.map(H=>K(U,H))},ee=(S,U)=>({invalid:!!K((U||i).errors,S),isDirty:!!K((U||i).dirtyFields,S),error:K((U||i).errors,S),isValidating:!!K(i.validatingFields,S),isTouched:!!K((U||i).touchedFields,S)}),ne=S=>{S&&ei(S).forEach(U=>ot(i.errors,U)),M.state.next({errors:S?i.errors:{}})},ye=(S,U,H)=>{const J=(K(u,S,{_f:{}})._f||{}).ref,P=K(i.errors,S)||{},{ref:Z,message:I,type:ze,...Xe}=P;qe(i.errors,S,{...Xe,...U,ref:J}),M.state.next({name:S,errors:i.errors,isValid:!1}),H&&H.shouldFocus&&J&&J.focus&&J.focus()},de=(S,U)=>cn(S)?M.state.subscribe({next:H=>S(ge(void 0,U),H)}):ge(S,U,!0),$e=S=>M.state.subscribe({next:U=>{U5(S.name,U.name,S.exact)&&N5(U,S.formState||g,$l,S.reRenderRoot)&&S.callback({values:{...f},...i,...U})}}).unsubscribe,Se=S=>(m.mount=!0,z={...z,...S.formState},$e({...S,formState:z})),Me=(S,U={})=>{for(const H of S?ei(S):v.mount)v.mount.delete(H),v.array.delete(H),U.keepValue||(ot(u,H),ot(f,H)),!U.keepError&&ot(i.errors,H),!U.keepDirty&&ot(i.dirtyFields,H),!U.keepTouched&&ot(i.touchedFields,H),!U.keepIsValidating&&ot(i.validatingFields,H),!r.shouldUnregister&&!U.keepDefaultValue&&ot(s,H);M.state.next({values:gt(f)}),M.state.next({...i,...U.keepDirty?{isDirty:re()}:{}}),!U.keepIsValid&&q()},Ne=({disabled:S,name:U})=>{(Bt(S)&&m.mount||S||v.disabled.has(U))&&(S?v.disabled.add(U):v.disabled.delete(U))},Dt=(S,U={})=>{let H=K(u,S);const J=Bt(U.disabled)||Bt(r.disabled);return qe(u,S,{...H||{},_f:{...H&&H._f?H._f:{ref:{name:S}},name:S,mount:!0,...U}}),v.mount.add(S),H?Ne({disabled:Bt(U.disabled)?U.disabled:r.disabled,name:S}):me(S,!0,U.value),{...J?{disabled:U.disabled||r.disabled}:{},...r.progressive?{required:!!U.required,min:Fr(U.min),max:Fr(U.max),minLength:Fr(U.minLength),maxLength:Fr(U.maxLength),pattern:Fr(U.pattern)}:{},name:S,onChange:Je,onBlur:Je,ref:P=>{if(P){Dt(S,U),H=K(u,S);const Z=Ie(P.value)&&P.querySelectorAll&&P.querySelectorAll("input,select,textarea")[0]||P,I=D5(Z),ze=H._f.refs||[];if(I?ze.find(Xe=>Xe===Z):Z===H._f.ref)return;qe(u,S,{_f:{...H._f,...I?{refs:[...ze.filter(As),Z,...Array.isArray(K(s,S))?[{}]:[]],ref:{type:Z.type,name:S}}:{ref:Z}}}),me(S,!1,void 0,Z)}else H=K(u,S,{}),H._f&&(H._f.mount=!1),(r.shouldUnregister||U.shouldUnregister)&&!(Hp(v.array,S)&&m.action)&&v.unMount.add(S)}}},Fn=()=>r.shouldFocusError&&ti(u,T,v.mount),hn=S=>{Bt(S)&&(M.state.next({disabled:S}),ti(u,(U,H)=>{const J=K(u,H);J&&(U.disabled=J._f.disabled||S,Array.isArray(J._f.refs)&&J._f.refs.forEach(P=>{P.disabled=J._f.disabled||S}))},0,!1))},Ta=(S,U)=>async H=>{let J;H&&(H.preventDefault&&H.preventDefault(),H.persist&&H.persist());let P=gt(f);if(M.state.next({isSubmitting:!0}),r.resolver){const{errors:Z,values:I}=await G();i.errors=Z,P=I}else await ce(u);if(v.disabled.size)for(const Z of v.disabled)qe(P,Z,void 0);if(ot(i.errors,"root"),Tt(i.errors)){M.state.next({errors:{}});try{await S(P,H)}catch(Z){J=Z}}else U&&await U({...i.errors},H),Fn(),setTimeout(Fn);if(M.state.next({isSubmitted:!0,isSubmitting:!1,isSubmitSuccessful:Tt(i.errors)&&!J,submitCount:i.submitCount+1,errors:i.errors}),J)throw J},el=(S,U={})=>{K(u,S)&&(Ie(U.defaultValue)?Ve(S,gt(K(s,S))):(Ve(S,U.defaultValue),qe(s,S,gt(U.defaultValue))),U.keepTouched||ot(i.touchedFields,S),U.keepDirty||(ot(i.dirtyFields,S),i.isDirty=U.defaultValue?re(S,gt(K(s,S))):re()),U.keepError||(ot(i.errors,S),g.isValid&&q()),M.state.next({...i}))},En=(S,U={})=>{const H=S?gt(S):s,J=gt(H),P=Tt(S),Z=P?s:J;if(U.keepDefaultValues||(s=H),!U.keepValues){if(U.keepDirtyValues){const I=new Set([...v.mount,...Object.keys(Pr(s,f))]);for(const ze of Array.from(I))K(i.dirtyFields,ze)?qe(Z,ze,K(f,ze)):Ve(ze,K(Z,ze))}else{if(lf&&Ie(S))for(const I of v.mount){const ze=K(u,I);if(ze&&ze._f){const Xe=Array.isArray(ze._f.refs)?ze._f.refs[0]:ze._f.ref;if(Zo(Xe)){const ct=Xe.closest("form");if(ct){ct.reset();break}}}}for(const I of v.mount)Ve(I,K(Z,I))}f=gt(Z),M.array.next({values:{...Z}}),M.state.next({values:{...Z}})}v={mount:U.keepDirtyValues?v.mount:new Set,unMount:new Set,array:new Set,disabled:new Set,watch:new Set,watchAll:!1,focus:""},m.mount=!g.isValid||!!U.keepIsValid||!!U.keepDirtyValues,m.watch=!!r.shouldUnregister,M.state.next({submitCount:U.keepSubmitCount?i.submitCount:0,isDirty:P?!1:U.keepDirty?i.isDirty:!!(U.keepDefaultValues&&!xa(S,s)),isSubmitted:U.keepIsSubmitted?i.isSubmitted:!1,dirtyFields:P?{}:U.keepDirtyValues?U.keepDefaultValues&&f?Pr(s,f):i.dirtyFields:U.keepDefaultValues&&S?Pr(s,S):U.keepDirty?i.dirtyFields:{},touchedFields:U.keepTouched?i.touchedFields:{},errors:U.keepErrors?i.errors:{},isSubmitSuccessful:U.keepIsSubmitSuccessful?i.isSubmitSuccessful:!1,isSubmitting:!1})},tl=(S,U)=>En(cn(S)?S(f):S,U),nl=(S,U={})=>{const H=K(u,S),J=H&&H._f;if(J){const P=J.refs?J.refs[0]:J.ref;P.focus&&(P.focus(),U.shouldSelect&&cn(P.select)&&P.select())}},$l=S=>{i={...i,...S}},al={control:{register:Dt,unregister:Me,getFieldState:ee,handleSubmit:Ta,setError:ye,_subscribe:$e,_runSchema:G,_getWatch:ge,_getDirty:re,_setValid:q,_setFieldArray:V,_setDisabledField:Ne,_setErrors:$,_getFieldArray:Te,_reset:En,_resetDefaultValues:()=>cn(r.defaultValues)&&r.defaultValues().then(S=>{tl(S,r.resetOptions),M.state.next({isLoading:!1})}),_removeUnmounted:oe,_disableForm:hn,_subjects:M,_proxyFormState:g,get _fields(){return u},get _formValues(){return f},get _state(){return m},set _state(S){m=S},get _defaultValues(){return s},get _names(){return v},set _names(S){v=S},get _formState(){return i},get _options(){return r},set _options(S){r={...r,...S}}},subscribe:Se,trigger:Y,register:Dt,handleSubmit:Ta,watch:de,setValue:Ve,getValues:ue,reset:tl,resetField:el,clearErrors:ne,unregister:Me,setError:ye,setFocus:nl,getFieldState:ee};return{...al,formControl:al}}function hx(a={}){const r=X.useRef(void 0),i=X.useRef(void 0),[u,s]=X.useState({isDirty:!1,isValidating:!1,isLoading:cn(a.defaultValues),isSubmitted:!1,isSubmitting:!1,isSubmitSuccessful:!1,isValid:!1,submitCount:0,dirtyFields:{},touchedFields:{},validatingFields:{},errors:a.errors||{},disabled:a.disabled||!1,isReady:!1,defaultValues:cn(a.defaultValues)?void 0:a.defaultValues});r.current||(r.current={...a.formControl?a.formControl:V5(a),formState:u},a.formControl&&a.defaultValues&&!cn(a.defaultValues)&&a.formControl.reset(a.defaultValues,a.resetOptions));const f=r.current.control;return f._options=a,uf(()=>{const m=f._subscribe({formState:f._proxyFormState,callback:()=>s({...f._formState}),reRenderRoot:!0});return s(v=>({...v,isReady:!0})),f._formState.isReady=!0,m},[f]),X.useEffect(()=>f._disableForm(a.disabled),[f,a.disabled]),X.useEffect(()=>{a.mode&&(f._options.mode=a.mode),a.reValidateMode&&(f._options.reValidateMode=a.reValidateMode),a.errors&&!Tt(a.errors)&&f._setErrors(a.errors)},[f,a.errors,a.mode,a.reValidateMode]),X.useEffect(()=>{a.shouldUnregister&&f._subjects.state.next({values:f._getWatch()})},[f,a.shouldUnregister]),X.useEffect(()=>{if(f._proxyFormState.isDirty){const m=f._getDirty();m!==u.isDirty&&f._subjects.state.next({isDirty:m})}},[f,u.isDirty]),X.useEffect(()=>{a.values&&!xa(a.values,i.current)?(f._reset(a.values,f._options.resetOptions),i.current=a.values,s(m=>({...m}))):f._resetDefaultValues()},[f,a.values]),X.useEffect(()=>{f._state.mount||(f._setValid(),f._state.mount=!0),f._state.watch&&(f._state.watch=!1,f._subjects.state.next({...f._formState})),f._removeUnmounted()}),r.current.formState=Bp(u,f),r.current}function ff(a){return a==null}function $p(a){return Object.prototype.toString.call(a)==="[object RegExp]"}function js(a){return Array.isArray(a)}function Ts(a){return typeof a=="function"}function Pp(a){return{set:(r,i)=>(a.setAttribute(r,i),Pp(a))}}function Um(a=100){let r;return new Promise((i,u)=>{r=setTimeout(()=>{i(),ff(r)||clearTimeout(r)},a)})}function qm(a,r){return(js(a)?a:ff(a)?[]:[a]).some(i=>$p(i)?i.test(r):r===i)}function _s(a){setTimeout(a,0)}const j5=y.createContext({active:!1,refresh:()=>{},destroy:()=>Promise.resolve(),destroyAll:()=>Promise.resolve(),destroyOther:()=>Promise.resolve(),getCacheNodes:()=>[]}),Y5=y.memo(function(a){const{children:r,active:i,refresh:u,destroy:s,destroyAll:f,destroyOther:m,getCacheNodes:v}=a,h=y.useMemo(()=>({active:i,refresh:u,destroy:s,destroyAll:f,destroyOther:m,getCacheNodes:v}),[i,u,s,f,m,v]);return jl.jsx(j5.Provider,{value:h,children:r})});function X5(a,r,i,u){return new(i||(i=Promise))(function(s,f){function m(p){try{h(u.next(p))}catch(g){f(g)}}function v(p){try{h(u.throw(p))}catch(g){f(g)}}function h(p){var g;p.done?s(p.value):(g=p.value,g instanceof i?g:new i(function(z){z(g)})).then(m,v)}h((u=u.apply(a,[])).next())})}const Fp="keepalive-cache-div";function Kp(a){return a?Array.from(a.children):[]}function Ys(a){a.forEach(r=>{r.classList.contains(Fp)&&r.remove()})}function Hm(a,r){Ys(Kp(a)),a.appendChild(r),r.classList.remove("inactive"),r.classList.add("active")}function Lm(a,r){const i=Kp(a).filter(u=>u.classList.contains("active")&&u.getAttribute("data-cache-key")!==r);return i.forEach(u=>{u.classList.remove("active"),u.classList.add("inactive")}),i}const G5=y.memo(function(a){const{errorElement:r=y.Fragment,cacheNodeClassName:i,children:u,cacheKey:s,exclude:f,include:m}=a,{active:v,renderCount:h,destroy:p,transition:g,viewTransition:z,duration:M,containerDivRef:w}=a,A=y.useRef(!1);A.current=A.current||v;const E=y.useMemo(()=>{const R=document.createElement("div");return Pp(R).set("data-cache-key",s).set("style","height: 100%").set("data-render-count",h.toString()),R.className=Fp+(i?` ${i}`:""),R},[h,i]);return y.useEffect(()=>{const R=function(N,V,F){return F?qm(F,N):!V||!qm(V,N)}(s,f,m),q=w.current;if(q)if(g)X5(this,void 0,void 0,function*(){if(v){const N=Lm(q,s);if(yield Um(M-40),Ys(N),q.contains(E))return;Hm(q,E)}else R||(yield Um(M),p(s))});else if(v){const N=()=>{Ys(Lm(q,s)),q.contains(E)||Hm(q,E)};z&&document.startViewTransition?document.startViewTransition(N):N()}else R||p(s);else console.warn("keepalive: cache container not found")},[v,w,s,f,m]),A.current?tp.createPortal(jl.jsx(r,{children:u}),E,s):null},(a,r)=>a.active===r.active&&a.renderCount===r.renderCount&&a.children===r.children&&a.exclude===r.exclude&&a.include===r.include);function mx(){return y.useRef()}function px(a){const{activeCacheKey:r,max:i=10,exclude:u,include:s,onBeforeActive:f,customContainerRef:m,cacheNodeClassName:v="cache-component",containerClassName:h="keep-alive-render",errorElement:p,transition:g=!1,viewTransition:z=!1,duration:M=200,children:w,aliveRef:A,maxAliveTime:E=0}=a,R=m||y.useRef(null),[q,N]=y.useState([]);y.useLayoutEffect(()=>{var ve;ff(r)||(ve=()=>{N(G=>{const W=Date.now();if(G.find(ce=>ce.cacheKey===r))return G.map(ce=>{if(ce.cacheKey===r){let oe=!1;if(Ts(f)&&f(r),E){const re=ce.lastActiveTime;if(js(E)){const ge=E.find(Te=>$p(Te.match)?Te.match.test(r):Te.match===r);ge&&(oe=ge&&re+1e3*ge.expire<W)}else oe=re+1e3*E<W}return Object.assign(Object.assign({},ce),{ele:w,lastActiveTime:W,renderCount:oe?ce.renderCount+1:ce.renderCount})}return ce});if(Ts(f)&&f(r),G.length>i){const ce=G.reduce((oe,re)=>oe.lastActiveTime<re.lastActiveTime?oe:re);G.splice(G.indexOf(ce),1)}return[...G,{cacheKey:r,lastActiveTime:W,ele:w,renderCount:0}]})},y.startTransition!==void 0&&Ts(y.startTransition)?y.startTransition(ve):ve())},[r,w]);const V=y.useCallback(ve=>{N(G=>{const W=ve||r;return G.map(ce=>ce.cacheKey===W?Object.assign(Object.assign({},ce),{renderCount:ce.renderCount+1}):ce)})},[N,r]),F=y.useCallback(ve=>{const G=ve||r,W=js(G)?G:[G];return new Promise(ce=>{_s(()=>{N(oe=>[...oe.filter(re=>!W.includes(re.cacheKey))]),ce()})})},[N,r]),$=y.useCallback(()=>new Promise(ve=>{_s(()=>{N([]),ve()})}),[N]),me=y.useCallback(ve=>{const G=ve||r;return new Promise(W=>{_s(()=>{N(ce=>[...ce.filter(oe=>oe.cacheKey===G)]),W()})})},[r,N]),pe=y.useCallback(()=>q,[q]);return y.useImperativeHandle(A,()=>({refresh:V,destroy:F,destroyAll:$,destroyOther:me,getCacheNodes:pe})),jl.jsxs(y.Fragment,{children:[jl.jsx("div",{ref:R,className:h,style:{height:"100%"}}),q.map(ve=>{const{cacheKey:G,ele:W,renderCount:ce}=ve;return jl.jsx(Y5,{active:r===G,refresh:V,destroy:F,destroyAll:$,destroyOther:me,getCacheNodes:pe,children:jl.jsx(G5,{destroy:F,include:s,exclude:u,transition:g,viewTransition:z,duration:M,renderCount:ce,containerDivRef:R,errorElement:p,active:r===G,cacheNodeClassName:v,cacheKey:G,children:W})},`${G}-${ce}`)})]})}export{J5 as A,W5 as B,dx as C,sy as D,$5 as E,wp as F,Im as L,F5 as N,K5 as O,X as R,K as a,z5 as b,ux as c,ax as d,ox as e,lx as f,mg as g,fx as h,rx as i,jl as j,cx as k,hx as l,T5 as m,ix as n,sx as o,P5 as p,Pn as q,y as r,qe as s,Kg as t,$s as u,mx as v,px as w,I5 as x,nx as y,b2 as z};
