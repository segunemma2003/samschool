import{c as p,r as e,j as v,l as d}from"./client-BFsy8bn8.js";/**
 * @license lucide-react v0.482.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const f=[["path",{d:"m12 19-7-7 7-7",key:"1l729n"}],["path",{d:"M19 12H5",key:"x3x0zl"}]],O=p("ArrowLeft",f);function R(r){const t=e.useRef({value:r,previous:r});return e.useMemo(()=>(t.current.value!==r&&(t.current.previous=t.current.value,t.current.value=r),t.current.previous),[r])}var l="Separator",n="horizontal",m=["horizontal","vertical"],i=e.forwardRef((r,t)=>{const{decorative:s,orientation:a=n,...c}=r,o=x(a)?a:n,u=s?{role:"none"}:{"aria-orientation":o==="vertical"?o:void 0,role:"separator"};return v.jsx(d.div,{"data-orientation":o,...u,...c,ref:t})});i.displayName=l;function x(r){return m.includes(r)}var E=i;export{O as A,E as R,R as u};
