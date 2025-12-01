package p5.educationalstrange.tuple;

import java.util.ArrayList;

public class EMergedTokenTuple extends ETokenTuple {
// this is n-gram tokens per code file.
	private ArrayList<ETokenTuple> members;
// to determine whether the tokens can be merged
	private int firstTokenIndex;

	public EMergedTokenTuple(int firstTokenIndex, ETokenTuple p1) {
		super(p1.getText(), "MERGED", p1.getLine());
		// TODO Auto-generated constructor stub
		this.firstTokenIndex = firstTokenIndex;
		this.members = new ArrayList<>();
		this.members.add(p1);
	}

	public ArrayList<ETokenTuple> getMembers() {
		return members;
	}

	public void setMembers(ArrayList<ETokenTuple> members) {
		this.members = members;
	}

	public int getFirstTokenIndex() {
		return firstTokenIndex;
	}

	public void setFirstTokenIndex(int firstTokenIndex) {
		this.firstTokenIndex = firstTokenIndex;
	}

	public String getText() {
		String out = "";
		for (int i = 0; i < members.size(); i++) {
			Object tt = members.get(i);
			if (tt instanceof ETokenTuple) {
				out = out + ((ETokenTuple) tt).getText() + " ";
			} else {
				System.out.println(tt.toString());
			}
		}
		return out;
	}

	@Override
	public int compareTo(ETokenTuple arg0) {
		// TODO Auto-generated method stub
		return this.getFirstTokenIndex() - ((EMergedTokenTuple) arg0).getFirstTokenIndex();
	}
}