"""GR28-style curved-jaw star coupling: 2 keyed hubs, 8-leg spider, shafts and keys, as a new design."""
import math
import adsk.core
import adsk.fusion

CM = 0.1  # mm -> cm
P = lambda x, y, z=0: adsk.core.Point3D.create(x * CM, y * CM, z * CM)
VI = lambda mm: adsk.core.ValueInput.createByReal(mm * CM)

OD, BORE_R, HUB_L, JAW_L = 65.0, 12.0, 30.0, 18.0
JAW_RI, JAW_W = 17.5, 28.0                # jaw inner radius, angular width (deg)
SP_RI, SP_RING, SP_RO, LEG_W = 13.0, 17.0, 32.0, 15.0
KEY_W, KEY_TOP, KEYSEAT = 8.0, 15.3, 8.0  # key width, hub keyway top (from axis), shaft keyseat bottom
NEW = adsk.fusion.FeatureOperations.NewBodyFeatureOperation
CUT = adsk.fusion.FeatureOperations.CutFeatureOperation
JOIN = adsk.fusion.FeatureOperations.JoinFeatureOperation


def plane(comp, z):
    if z == 0:
        return comp.xYConstructionPlane
    pi = comp.constructionPlanes.createInput()
    pi.setByOffset(comp.xYConstructionPlane, VI(z))
    pl = comp.constructionPlanes.add(pi)
    pl.isLightBulbOn = False
    return pl


def extrude(comp, profiles, h, op, bodies=None, name=None):
    ext = comp.features.extrudeFeatures
    if isinstance(profiles, list):
        coll = adsk.core.ObjectCollection.create()
        for p in profiles:
            coll.add(p)
        profiles = coll
    inp = ext.createInput(profiles, op)
    inp.setDistanceExtent(False, VI(h))
    if bodies:
        inp.participantBodies = bodies
    f = ext.add(inp)
    if name and op == NEW:
        f.bodies.item(0).name = name
    return f


def annulus(comp, z, h, ro, ri, op, name=None, bodies=None):
    sk = comp.sketches.add(plane(comp, z))
    sk.sketchCurves.sketchCircles.addByCenterRadius(P(0, 0), ro * CM)
    if ri:
        sk.sketchCurves.sketchCircles.addByCenterRadius(P(0, 0), ri * CM)
        prof = [p for p in sk.profiles if p.profileLoops.count == 2][0]
    else:
        prof = sk.profiles.item(0)
    return extrude(comp, prof, h, op, bodies, name)


def sectors(comp, z, h, ri, ro, centres, width, op, name=None, bodies=None):
    sk = comp.sketches.add(plane(comp, z))
    arcs, lines = sk.sketchCurves.sketchArcs, sk.sketchCurves.sketchLines
    for c in centres:
        a1 = math.radians(c - width / 2)
        sw = math.radians(width)
        o = arcs.addByCenterStartSweep(P(0, 0), P(ro * math.cos(a1), ro * math.sin(a1)), sw)
        i = arcs.addByCenterStartSweep(P(0, 0), P(ri * math.cos(a1), ri * math.sin(a1)), sw)
        lines.addByTwoPoints(o.startSketchPoint, i.startSketchPoint)
        lines.addByTwoPoints(o.endSketchPoint, i.endSketchPoint)
    return extrude(comp, [p for p in sk.profiles], h, op, bodies, name)


def rect(comp, z, h, x0, y0, x1, y1, op, name=None, bodies=None):
    sk = comp.sketches.add(plane(comp, z))
    sk.sketchCurves.sketchLines.addTwoPointRectangle(P(x0, y0), P(x1, y1))
    return extrude(comp, sk.profiles.item(0), h, op, bodies, name)


def set_screw(comp, body, z):
    """Radial M8 tapping hole over the keyway (+Y)."""
    sk = comp.sketches.add(comp.xZConstructionPlane)
    c = sk.modelToSketchSpace(P(0, 0, z))
    sk.sketchCurves.sketchCircles.addByCenterRadius(c, 0.34)
    ext = comp.features.extrudeFeatures
    for d in (OD, -OD):
        inp = ext.createInput(sk.profiles.item(0), CUT)
        inp.setDistanceExtent(False, VI(d / 2 + 1))
        inp.participantBodies = [body]
        try:
            ext.add(inp)
            return
        except Exception:
            pass


def edges_where(body, test):
    coll = adsk.core.ObjectCollection.create()
    for e in body.edges:
        if test(e):
            coll.add(e)
    return coll


def soften(comp, body, z_tip, z_back):
    """Chamfer the hub OD at the back face and round the jaw tips (best effort)."""
    try:
        circ = edges_where(body, lambda e: e.geometry.objectType == adsk.core.Circle3D.classType()
                           and abs(e.geometry.radius - OD / 2 * CM) < 1e-4 and abs(e.geometry.center.z - z_back * CM) < 1e-4)
        ci = comp.features.chamferFeatures.createInput2()
        ci.chamferEdgeSets.addEqualDistanceChamferEdgeSet(circ, VI(1.5), True)
        comp.features.chamferFeatures.add(ci)
    except Exception as ex:
        print("chamfer skipped:", ex)
    try:
        tip = edges_where(body, lambda e: abs(e.startVertex.geometry.z - z_tip * CM) < 1e-4
                          and abs(e.endVertex.geometry.z - z_tip * CM) < 1e-4)
        fi = comp.features.filletFeatures.createInput()
        fi.edgeSetInputs.addConstantRadiusEdgeSet(tip, VI(1.2), True)
        comp.features.filletFeatures.add(fi)
    except Exception as ex:
        print("fillet skipped:", ex)


def new_comp(root, name):
    occ = root.occurrences.addNewComponent(adsk.core.Matrix3D.create())
    occ.component.name = name
    return occ.component


def hub(root, name, z_body, z_jaw, jaw_angles, z_tip, z_back):
    comp = new_comp(root, name)
    body = annulus(comp, z_body, HUB_L, OD / 2, BORE_R, NEW, name).bodies.item(0)
    sectors(comp, z_jaw, JAW_L, JAW_RI, OD / 2, jaw_angles, JAW_W, JOIN, bodies=[body])
    rect(comp, z_body, HUB_L, -KEY_W / 2, 0, KEY_W / 2, KEY_TOP, CUT, bodies=[body])
    set_screw(comp, body, z_body + HUB_L / 2)
    soften(comp, body, z_tip, z_back)
    return comp, body


def shaft(root, name, z0, length, key_z0, key_len):
    comp = new_comp(root, name)
    body = annulus(comp, z0, length, BORE_R, 0, NEW, name).bodies.item(0)
    rect(comp, key_z0, key_len, -KEY_W / 2, KEYSEAT, KEY_W / 2, BORE_R + 1, CUT, bodies=[body])
    kc = new_comp(root, name.replace("Shaft", "Key"))
    rect(kc, key_z0, key_len, -KEY_W / 2, KEYSEAT, KEY_W / 2, KEY_TOP - 0.1, NEW, kc.name)
    return comp, body, kc


def run(_context: str):
    app = adsk.core.Application.get()
    doc = app.documents.add(adsk.core.DocumentTypes.FusionDesignDocumentType)
    doc.name = "MGI XL-GR28 Star Coupling"
    design = adsk.fusion.Design.cast(app.activeProduct)
    design.designType = adsk.fusion.DesignTypes.ParametricDesignType
    root = design.rootComponent
    # Hub A: body z 0..30, jaws z 30..48 at 0/90/180/270.  Hub B mirrored, jaws offset 45 deg.
    hub(root, "Hub A (keyed, Ø24 H7)", 0, HUB_L, [0, 90, 180, 270], HUB_L + JAW_L, 0)
    hub(root, "Hub B (keyed, Ø24 H7)", 50, 32, [45, 135, 225, 315], 32, 80)
    sp = new_comp(root, "Spider 92 ShA (8 legs)")
    body = annulus(sp, 31, 18, SP_RING, SP_RI, NEW, "Spider 92 ShA").bodies.item(0)
    sectors(sp, 31, 18, SP_RING - 0.5, SP_RO, [22.5 + 45 * k for k in range(8)], LEG_W, JOIN, bodies=[body])
    try:
        tip = edges_where(body, lambda e: e.geometry.objectType == adsk.core.Line3D.classType()
                          and abs(e.startVertex.geometry.z - e.endVertex.geometry.z) > 1e-3)
        fi = sp.features.filletFeatures.createInput()
        fi.edgeSetInputs.addConstantRadiusEdgeSet(tip, VI(1.0), True)
        sp.features.filletFeatures.add(fi)
    except Exception as ex:
        print("spider fillet skipped:", ex)
    shaft(root, "Shaft A (motor)", -70, 98, 3, 24)
    shaft(root, "Shaft B (pump)", 52, 98, 53, 24)
    app.activeViewport.fit()
    for occ in root.occurrences:
        b = occ.bRepBodies.item(0)
        print("%-26s vol %.1f cm3  faces %d" % (occ.component.name, b.volume, b.faces.count))
